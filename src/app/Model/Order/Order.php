<?php

namespace App\Model\Order;

use App\Model\Stock\Stock;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Order\Order
 *
 * @property int $id
 * @property int $sequence
 * @property int $stock_id
 * @property string $date
 * @property string $type
 * @property int $quantity
 * @property float $price
 * @property float $cost
 * @property float $average_price
 */

class Order extends Model {

    public function store(
        Stock $stock,
        Carbon $date,
        string $type,
        int $quantity,
        float $price,
        float $cost
    ): void {
        $this->stock_id = $stock->id;
        $this->date = $date->toDateString();
        $this->type = $type;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->cost = $cost;
        $this->average_price = $this->calculateAveragePrice();
        $this->save(['should_increment_sequence' => true]);
    }

    public static function createOrder(
        string $stock_symbol,
        Carbon $date,
        string $type,
        int $quantity,
        float $price,
        float $cost
    ): self {
        $stock = Stock::firstOrCreate(['symbol' => $stock_symbol]);

        $order = new Order();
        $order->store(
            $stock,
            $date,
            $type,
            $quantity,
            $price,
            $cost
        );

        return $order;
    }

    public function getStockSymbol(): string {
        $stock = Stock::find($this->stock_id);

        return $stock->symbol;
    }

    public function getTotal(): string {
        return $this->calculateTotal();
    }

    public static function consolidateQuantityForStock(Stock $stock): int {
        $orders = self::where('stock_id', $stock->id)->get();

        $quantity = 0;
        /** @var Order $order */
        foreach ($orders as $order) {
            $type_modifier = self::getTypeModifier($order->type);
            $quantity += $order->quantity * $type_modifier;
        }

        return $quantity;
    }

    public static function getDateOfFirstContribution(Stock $stock = null): ?Carbon {
        $query = self::query();

        if($stock) {
            $query->where('stock_id', $stock->id);
        }

        /** @var Order $order */
        $order = $query->orderBy('date')->get()->first();

        return $order ? Carbon::parse($order->date) : null;
    }

    public static function getAllOrdersForStock(Stock $stock): Collection {
        return self::where('stock_id', $stock->id)->orderBy('sequence')->get();
    }

    private function calculateAveragePrice(): float {
        return $this->getTotal() / $this->quantity;
    }

    private function calculateTotal(): float {
        $type_modifier = self::getTypeModifier($this->type);

        return (($this->quantity * $this->price) + ($this->cost * $type_modifier));
    }

    public static function getTypeModifier(string $type): int {
        if($type == 'sell') {
            return -1;
        }

        return 1;
    }

    public function save(array $options = []) {
        if(isset($options['should_increment_sequence'])) {
            $this->sequence = (self::max('sequence') ?? 0) + 1;
        }

        return parent::save($options);
    }

    public function delete() {
        parent::delete();

        $this->updatePrecedingOrdersSequence();
    }

    private function updatePrecedingOrdersSequence() {
        $preceding_orders = self::where('sequence', '>', $this->sequence)->orderBy('sequence')->get();

        /** @var Order $order */
        foreach ($preceding_orders as $order) {
            $order->sequence = $order->sequence - 1;
            $order->save();
        }
    }
}
