<?php

namespace App\Model\Order;

use App\Model\Stock\Stock;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Order\Order
 *
 * @property int $id
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
        $this->save();
    }

    private function calculateAveragePrice(): float {
        $type_modifier = 1;

        if($this->type == 'sell') {
            $type_modifier = -1;
        }

        return (($this->quantity * $this->price) + ($this->cost * $type_modifier)) / $this->quantity;
    }
}
