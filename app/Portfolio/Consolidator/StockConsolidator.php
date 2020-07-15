<?php

namespace App\Portfolio\Consolidator;

use App\Model\Log\Log;
use App\Model\Order\Order;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Model\Stock\StockPrice;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;

class StockConsolidator {

    /** @var Stock $stock */
    private static $stock;
    private static $prices;
    private static $positions_buffer = [];

    public static function updateLastPositions(): void {
        $stocks = Order::getAllStocksWithOrders();

        foreach ($stocks as $stock) {
            self::updateLastPositionForStock($stock);
        }

        self::removePositionsWithoutOrders($stocks);
        self::touchLastStockPosition();
    }

    public static function updateLastPositionForStock(Stock $stock) {
        self::$stock = $stock;
        $date = Calendar::getLastMarketWorkingDate();
        $orders = Order::getAllOrdersForStockUntilDate($stock, $date);
        self::loadAndCachePricesBeforeProcessing([$date->toDateString()]);

        $position['date'] = $date;
        $position = self::sumOrdersToPosition($orders->toArray(), $position);
        $position = self::calculateAmountAccordinglyPriceOnDate($date, $position);

        self::$positions_buffer[] = $position;
        self::savePositionsBuffer();
    }

    public static function consolidate(): void {
        ini_set('max_execution_time', '300');
        Log::log('debug', __CLASS__.'::'.__FUNCTION__, 'starting at:' . Carbon::now()->toDateTimeString());
        $stocks = Order::getAllStocksWithOrders();

        foreach ($stocks as $stock) {
            self::consolidateForStock($stock);
        }

        self::removePositionsWithoutOrders($stocks);
        self::touchLastStockPosition();
        Log::log('debug', __CLASS__.'::'.__FUNCTION__, 'finishing at:' . Carbon::now()->toDateTimeString());
    }

    public static function consolidateForStock(Stock $stock): void {
        self::consolidateStockPositionsForStock($stock);
    }

    private static function consolidateStockPositionsForStock(Stock $stock) {
        self::$stock = $stock;
        $end_date = Calendar::getLastMarketWorkingDate();
        $dates = self::generateAllDatesAccordinglyDayOfFirstContribution($end_date);
        $grouped_orders = self::getOrdersGroupedByDateUntilDate($end_date);
        self::loadAndCachePricesBeforeProcessing($dates);

        foreach ($dates as $date) {
            $position = isset($position) ? $position : [];
            $position['date'] = Carbon::parse($date);

            if(isset($grouped_orders[$date])) {
                $orders_in_this_date = $grouped_orders[$date];
                $position = self::sumOrdersToPosition($orders_in_this_date, $position);
            }

            $position = self::calculateAmountAccordinglyPriceOnDate(Carbon::parse($date), $position);

            self::$positions_buffer[] = $position;
        }

        self::savePositionsBuffer();
    }

    private static function getOrdersGroupedByDateUntilDate(Carbon $end_date): array {
        $orders = Order::getAllOrdersForStockUntilDate(self::$stock, $end_date);

        $grouped_orders = [];
        /** @var Order $order */
        foreach ($orders as $order) {
            $grouped_orders[$order->date][] = $order;
        }

        return $grouped_orders;
    }

    private static function loadAndCachePricesBeforeProcessing(array $dates): void {
        if(sizeof($dates) < 1) {
            return;
        }

        $start_date = Carbon::parse($dates[0]);
        $end_date = Carbon::parse($dates[sizeof($dates)-1]);

        $stock_prices = StockPrice::getStockPricesForDateRange(self::$stock, $start_date, $end_date);

        self::$prices = [];
        /** @var StockPrice $stock_price */
        foreach ($stock_prices as $stock_price) {
            self::$prices[$stock_price['date']] = $stock_price['price'];
        }
    }

    private static function generateAllDatesAccordinglyDayOfFirstContribution(Carbon $end_date): array {
        $start_date = Order::getDateOfFirstContribution(self::$stock);
        if(!$start_date) {
            return [];
        }

        return Calendar::getWorkingDaysDatesForRange($start_date, $end_date);
    }

    private static function sumOrdersToPosition(array $orders, array $position): array {
        foreach ($orders as $order) {
            $position['quantity'] =
                (isset($position['quantity']) ? $position['quantity'] : 0) + $order['quantity'];
            $position['contributed_amount'] =
                (isset($position['contributed_amount']) ? $position['contributed_amount'] : 0) + $order['quantity'] * $order['price'] + $order['cost'];
        }

        $position['average_price'] = $position['contributed_amount']/$position['quantity'];

        return $position;
    }

    private static function calculateAmountAccordinglyPriceOnDate(Carbon $date, array $position): array {
        $price_on_date = self::getStockPriceFromCacheForDate($date);

        $position['amount'] = $price_on_date * $position['quantity'];

        return $position;
    }

    private static function getStockPriceFromCacheForDate(Carbon $date): float {
        if(!isset(self::$prices[$date->toDateString()])) {
            return 0;
        }

        return self::$prices[$date->toDateString()];
    }

    private static function savePositionsBuffer(): void {
        foreach (self::$positions_buffer as $position) {
            if($position['amount'] == 0) {
                continue;
            }

            StockPosition::updateOrCreate(
                [
                    'user_id'   => auth()->id(),
                    'stock_id'  => self::$stock->id,
                    'date'      => $position['date']->toDateString(),
                ],
                [
                    'quantity'              => $position['quantity'],
                    'amount'                => $position['amount'],
                    'contributed_amount'    => $position['contributed_amount'],
                    'average_price'         => $position['average_price'],
                ]
            );
        }

        self::$positions_buffer = [];
    }

    private static function removePositionsWithoutOrders(array $stocks): void {
        $stock_ids_to_remove = self::getStockIdsToRemove($stocks);

        StockPosition::getBaseQuery()->whereIn('stock_id', $stock_ids_to_remove)->delete();
    }

    private static function getStockIdsToRemove(array $stocks): array {
        $stock_ids_with_positions = StockPosition::getBaseQuery()->select('stock_id')->distinct('stock_id')->get();

        $stock_ids_with_positions = self::buildStockIdArray($stock_ids_with_positions->toArray(), 'stock_id');
        $stock_ids_with_orders = self::buildStockIdArray($stocks, 'id');

        return array_diff($stock_ids_with_positions, $stock_ids_with_orders);
    }

    private static function buildStockIdArray(array $data_to_pluck, string $field): array {
        $stock_ids = [];
        foreach ($data_to_pluck as $data) {
            $stock_ids[] = $data[$field];
        }

        return $stock_ids;
    }

    private static function touchLastStockPosition(): void {
        /** @var StockPosition $last_stock_position */
        $last_stock_position = StockPosition::getBaseQuery()->orderByDesc('id')->get()->first();
        if($last_stock_position) {
            $last_stock_position->touch();
        }
    }
}
