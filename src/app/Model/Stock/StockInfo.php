<?php

namespace App\Model\Stock;

use App\Portfolio\API\UolAPI;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Stock\StockInfo
 *
 * @property int $id
 * @property int $stock_id
 * @property string $date
 * @property float $price
 */

class StockInfo extends Model {

    public function store(Stock $stock, Carbon $date): void {
        $this->stock_id = $stock->id;
        $this->date = $date->toDateString();
        $this->price = UolAPI::getStockPriceForDate($stock->symbol, $date);
        $this->save();
    }
}
