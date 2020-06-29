<?php

namespace App\Model\Order;

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

}
