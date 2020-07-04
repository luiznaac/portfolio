<?php

namespace App\Model\Stock\Position;

use App\Model\Order\Order;
use App\Model\Stock\Stock;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Stock\StockPosition
 *
 * @property int $id
 * @property int $stock_id
 * @property string $date
 * @property int $quantity
 * @property float $amount
 * @property float $average_price
 */

class StockPosition extends Model {

    protected $fillable = ['symbol'];

    private static $positions_buffer;

    public static function consolidateFromBegin(int $stock_id) {
        $stock = Stock::find($stock_id);
        $orders = Order::getAllOrdersForStock($stock);

        /** @var Order $order */
        foreach ($orders as $order) {
            $actual_date = Carbon::parse($order->date);

            if (!isset($position) || (isset($previous_date) && $actual_date->notEqualTo($previous_date))) {
                $position = isset($position) ? clone $position : new self();
                self::$positions_buffer[$actual_date->toDateString()] = $position;
            }

            $position = self::sumOrderToPosition($order, $position);

            $previous_date = $actual_date;
        }

        self::fillInBetweenDates();
        self::savePositionsBuffer($stock_id);
    }

    private static function sumOrderToPosition(Order $order, StockPosition $position): StockPosition {
        $position->quantity = ($position->quantity ?: 0) + $order->quantity;
        $position->amount = ($position->amount ?: 0) + $order->quantity * $order->price;
        $position->average_price = ($position->average_price ?: 0) + $position->amount/$position->quantity;

        return $position;
    }

    private static function fillInBetweenDates(): void {
        $all_dates = self::generateAllDates();

        foreach ($all_dates as $index => $date) {
            if(!isset(self::$positions_buffer[$date])) {
                $previous_date = $all_dates[$index-1];
                $position_copy = clone self::$positions_buffer[$previous_date];

                self::$positions_buffer[$date] = $position_copy;
            }
        }
    }

    private static function generateAllDates(): array {
        $date = Carbon::parse(array_keys(self::$positions_buffer)[0]);
        $last_date = Carbon::parse(array_keys(self::$positions_buffer)[sizeof(self::$positions_buffer)-1]);

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

    private static function savePositionsBuffer(int $stock_id): void {
        /** @var StockPosition $position */
        foreach (self::$positions_buffer as $date => $position) {
            $position->stock_id = $stock_id;
            $position->date = $date;
            $position->save();
        }
    }
}
