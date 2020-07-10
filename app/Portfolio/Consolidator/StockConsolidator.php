<?php

namespace App\Portfolio\Consolidator;

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

    public static function updatePositions(): void {
        $stocks = Order::getAllStocksWithOrders();

        foreach ($stocks as $stock) {
            self::updatePositionForStock($stock);
        }
    }

    public static function updatePositionForStock(Stock $stock) {
        self::$stock = $stock;
        $date = Calendar::getLastWorkingDayForDate(Carbon::today()->subDay());
        $orders = Order::getAllOrdersForStockUntilDate($stock, $date);
        self::storeAndCachePricesBeforeProcessing([$date->toDateString()]);

        $position['date'] = $date;
        $position = self::sumOrdersToPosition($orders->toArray(), $position);
        $position = self::calculateAmountAccordinglyPriceOnDate($date, $position);

        self::$positions_buffer[] = $position;
        self::savePositionsBuffer();
    }

    public static function consolidateFromBegin(Stock $stock) {
        self::$stock = $stock;
        $end_date = Calendar::getLastWorkingDayForDate(Carbon::today()->subDay());
        $dates = self::generateAllDatesAccordinglyDayOfFirstContribution($end_date);
        $grouped_orders = self::getOrdersGroupedByDateUntilDate($end_date);
        self::storeAndCachePricesBeforeProcessing($dates);

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

    private static function storeAndCachePricesBeforeProcessing(array $dates): void {
        if(sizeof($dates) < 1) {
            return;
        }

        $start_date = Carbon::parse($dates[0]);
        $end_date = Carbon::parse($dates[sizeof($dates)-1]);

        StockPrice::storePricesForDates(self::$stock, $start_date, $end_date);

        $stock_prices = self::$stock->getStockPrices();

        self::$prices = [];
        /** @var StockPrice $stock_price */
        foreach ($stock_prices as $stock_price) {
            self::$prices[$stock_price->date] = $stock_price->price;
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
                (isset($position['contributed_amount']) ? $position['contributed_amount'] : 0) + $order['quantity'] * $order['price'];
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
}