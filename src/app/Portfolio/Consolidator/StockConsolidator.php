<?php

namespace App\Portfolio\Consolidator;

use App\Model\Order\Order;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class StockConsolidator {

    /** @var Stock $stock */
    private static $stock;
    private static $positions_buffer = [];

    public static function updatePositions(Carbon $date = null): void {
        $stocks = Order::getAllStocksWithOrders();

        foreach ($stocks as $stock) {
            self::updatePositionForStock($stock, $date);
        }
    }

    public static function updatePositionForStock(Stock $stock, Carbon $date = null) {
        self::$stock = $stock;
        $date = self::getLastWorkingDay($date);
        self::deletePositionsForStock($date);
        $orders = Order::getAllOrdersForStock($stock);

        $position = new StockPosition();
        $position->date = $date;
        $position = self::sumOrdersToPosition($orders->toArray(), $position);
        $position = self::calculateAmountAccordinglyPriceOnDate($date, $position);

        self::$positions_buffer[] = $position;
        self::savePositionsBuffer();
    }

    public static function consolidateFromBegin(Stock $stock, Carbon $end_date = null) {
        self::$stock = $stock;
        self::deletePositionsForStock();
        $orders = Order::getAllOrdersForStock($stock);
        $grouped_orders = self::groupOrdersByDate($orders);
        $dates = self::generateAllDates($end_date);
        $stock->loadStockPrices(Carbon::parse($dates[0]), Carbon::parse($dates[sizeof($dates)-1]));

        foreach ($dates as $date) {
            $position = isset($position) ? clone $position : new StockPosition();
            $position->date = $date;

            if(isset($grouped_orders[$date])) {
                $orders_in_this_date = $grouped_orders[$date];
                $position = self::sumOrdersToPosition($orders_in_this_date, $position);
            }

            $position = self::calculateAmountAccordinglyPriceOnDate($date, $position);

            self::$positions_buffer[] = $position;
        }

        self::savePositionsBuffer();
    }

    private static function deletePositionsForStock(Carbon $date = null): void {
        $query = StockPosition::query()
            ->where('stock_id', self::$stock->id);

        if(isset($date)) {
            $query->where('date', $date->toDateString());
        }

        $query->delete();
    }

    private static function groupOrdersByDate(Collection $orders): array {
        $grouped_orders = [];

        /** @var Order $order */
        foreach ($orders as $order) {
            $grouped_orders[$order->date][] = $order;
        }

        return $grouped_orders;
    }

    private static function generateAllDates(?Carbon $end_date): array {
        $date = Order::getDateOfFirstContribution(self::$stock);
        if(!$date) {
            return [];
        }

        $last_date = $end_date ?: Carbon::today();

        $all_dates = [];
        while($date->lte($last_date)) {
            $all_dates[] = $date->toDateString();

            $date->addDay();
            while($date->isWeekend()) {
                $date->addDay();
            }
        }

        return $all_dates;
    }

    private static function getLastWorkingDay(Carbon $date = null): Carbon {
        $date = $date ?: Carbon::yesterday();

        while($date->isWeekend()) {
            $date->subDay();
        }

        return $date;
    }

    private static function sumOrdersToPosition(array $orders, StockPosition $position): StockPosition {
        foreach ($orders as $order) {
            $position->quantity = ($position->quantity ?: 0) + $order['quantity'];
            $position->contributed_amount = ($position->contributed_amount ?: 0) + $order['quantity'] * $order['price'];
        }

        $position->average_price = $position->contributed_amount/$position->quantity;

        return $position;
    }

    private static function calculateAmountAccordinglyPriceOnDate(string $date, StockPosition $position): StockPosition {
        $date = Carbon::parse($date);
        $price_on_date = self::$stock->getStockPriceForDate($date);

        $position->amount = $price_on_date * $position->quantity;

        return $position;
    }

    private static function savePositionsBuffer(): void {
        /** @var StockPosition $position */
        foreach (self::$positions_buffer as $position) {
            $position->stock_id = self::$stock->id;
            $position->save();
        }

        self::$positions_buffer = [];
    }
}
