<?php

namespace App\Portfolio\Consolidator;

use App\Model\Order\Order;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Model\Stock\StockPrice;
use App\Portfolio\Utils\BatchInsertOrUpdate;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockPositionConsolidator implements ConsolidatorInterface {

    private static $prices;
    private static $positions_buffer = [];

    public static function consolidate(): void {
        self::removePositionsWithoutOrders();
        $stocks_dates = ConsolidatorDateProvider::getStockPositionDatesToBeUpdated();

        foreach ($stocks_dates as $stock_id => $date) {
            $stock = Stock::find($stock_id);
            $date = Carbon::parse($date);

            self::consolidateStockPositionsForStock($stock, $date);
        }

        self::savePositionsBuffer();
        self::touchLastStockPosition();
    }

    private static function consolidateStockPositionsForStock(Stock $stock, Carbon $start_date) {
        $end_date = Calendar::getLastMarketWorkingDate();
        $dates = Calendar::getWorkingDaysDatesForRange($start_date, $end_date);
        $grouped_orders = self::getOrdersGroupedByDateInRange($stock, $start_date, $end_date);
        self::loadAndCachePricesBeforeProcessing($stock, $dates);

        foreach ($dates as $date) {
            $position = $position ?? self::getStockPositionOrEmpty($stock, $start_date);
            $position['stock_id'] = $stock->id;
            $position['date'] = Carbon::parse($date);

            if(isset($grouped_orders[$date])) {
                $orders_in_this_date = $grouped_orders[$date];
                $position = self::sumOrdersToPosition($orders_in_this_date, $position);
            }

            $position = self::calculateAmountAccordinglyPriceOnDate(Carbon::parse($date), $position);

            self::$positions_buffer[] = $position;
        }
    }

    private static function getOrdersGroupedByDateInRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        $orders = Order::getAllOrdersForStockInRage($stock, $start_date, $end_date);

        $grouped_orders = [];
        /** @var Order $order */
        foreach ($orders as $order) {
            $grouped_orders[$order->date][] = $order;
        }

        return $grouped_orders;
    }

    private static function loadAndCachePricesBeforeProcessing(Stock $stock, array $dates): void {
        if(sizeof($dates) < 1) {
            return;
        }

        $start_date = Carbon::parse($dates[0]);
        $end_date = Carbon::parse($dates[sizeof($dates)-1]);

        $stock_prices = StockPrice::getStockPricesForDateRange($stock, $start_date, $end_date);

        self::$prices = [];
        /** @var StockPrice $stock_price */
        foreach ($stock_prices as $stock_price) {
            self::$prices[$stock_price['date']] = $stock_price['price'];
        }
    }

    private static function getStockPositionOrEmpty(Stock $stock, Carbon $date): array {
        $date = Calendar::getLastWorkingDayForDate((clone $date)->subDay());

        return StockPosition::getBaseQuery()
            ->where('stock_id', $stock->id)
            ->where('date', $date->toDateString())
            ->get()->toArray()[0] ?? [];
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
        $data = [];
        foreach (self::$positions_buffer as $position) {
            if($position['amount'] == 0) {
                continue;
            }

            $data[] = [
                'user_id'   => auth()->id(),
                'stock_id'  => $position['stock_id'],
                'date'      => $position['date']->toDateString(),
                'quantity'              => $position['quantity'],
                'amount'                => $position['amount'],
                'contributed_amount'    => $position['contributed_amount'],
                'average_price'         => $position['average_price'],
            ];
        }

        BatchInsertOrUpdate::execute('stock_positions', $data);
        self::$positions_buffer = [];
    }

    private static function removePositionsWithoutOrders(): void {
        $stock_position_ids_to_remove = self::getStockPositionIdsToRemove();

        StockPosition::getBaseQuery()->whereIn('id', $stock_position_ids_to_remove)->delete();
    }

    private static function getStockPositionIdsToRemove(): array {
        $query = <<<SQL
SELECT sp.id AS stock_position_id
FROM stock_positions sp
         LEFT JOIN orders o ON sp.stock_id = o.stock_id AND sp.user_id = o.user_id
WHERE sp.user_id = ?
  AND (o.id IS NULL
    OR sp.date < (SELECT MIN(date) FROM orders o2 WHERE sp.stock_id = o2.stock_id AND sp.user_id = o2.user_id));
SQL;

        $rows = DB::select($query, [auth()->id()]);

        $data = [];
        foreach ($rows as $row) {
            $data[] = $row->stock_position_id;
        }

        return $data;
    }

    private static function touchLastStockPosition(): void {
        /** @var StockPosition $last_stock_position */
        $last_stock_position = StockPosition::getBaseQuery()->orderByDesc('id')->get()->first();
        if($last_stock_position) {
            $last_stock_position->touch();
        }
    }
}
