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

    public static function storePricesForDates(Stock $stock, Carbon $start_date, Carbon $end_date): void {
        $prices = UolAPI::getStockPricesForRange($stock->symbol, $start_date, $end_date);

        foreach ($prices as $date => $price) {
            $stock_info = new self();
            $stock_info->stock_id = $stock->id;
            $stock_info->date = $date;
            $stock_info->price = $price;
            $stock_info->save();
        }
    }
}
