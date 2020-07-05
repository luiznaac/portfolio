<?php

namespace App\Model\Stock\Position;

use App\Model\Stock\Stock;
use Illuminate\Database\Eloquent\Collection;
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

    public static function getPositionsForStock(Stock $stock): Collection {
        return self::query()
            ->where('stock_id', $stock->id)
            ->orderByDesc('date')
            ->get();
    }
}
