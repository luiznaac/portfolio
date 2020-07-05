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

    public static function consolidateFromBegin(Stock $stock, Carbon $end_date = null) {
        self::$stock = $stock;
        self::deleteAllPositionsForStock();
        $orders = Order::getAllOrdersForStock($stock);
        $grouped_orders = self::groupOrdersByDate($orders);
        $dates = self::generateAllDates($end_date);

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

        self::savePositionsBuffer($stock->id);
    }

    private static function deleteAllPositionsForStock(): void {
        StockPosition::query()
            ->where('stock_id', self::$stock->id)
            ->delete();
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

    private static function sumOrdersToPosition(array $orders, StockPosition $position): StockPosition {
        foreach ($orders as $order) {
            $position->quantity = ($position->quantity ?: 0) + $order->quantity;
            $position->contributed_amount = ($position->contributed_amount ?: 0) + $order->quantity * $order->price;
            $position->average_price = ($position->average_price ?: 0) + $position->contributed_amount/$position->quantity;
        }

        return $position;
    }

    private static function calculateAmountAccordinglyPriceOnDate(string $date, StockPosition $position): StockPosition {
        $date = Carbon::parse($date);
        $price_on_date = self::$stock->getStockPriceForDate($date);

        $position->amount = $price_on_date * $position->quantity;

        return $position;
    }

    private static function savePositionsBuffer(int $stock_id): void {
        /** @var StockPosition $position */
        foreach (self::$positions_buffer as $position) {
            $position->stock_id = $stock_id;
            $position->save();
        }
    }
}
