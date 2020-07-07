<?php

namespace App\Model\Stock;

use App\Portfolio\API\UolAPI;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Stock\StockPrice
 *
 * @property int $id
 * @property int $stock_id
 * @property string $date
 * @property float $price
 */

class StockPrice extends Model {

    public function store(Stock $stock, Carbon $date): void {
        $this->stock_id = $stock->id;
        $this->date = $date->toDateString();
        $this->price = UolAPI::getStockPriceForDate($stock->symbol, $date);
        $this->save();
    }

    public static function storePricesForDates(Stock $stock, Carbon $start_date, Carbon $end_date): void {
        $prices = UolAPI::getStockPricesForRange($stock->symbol, $start_date, $end_date);

        foreach ($prices as $date => $price) {
            $stock_price = new self();
            $stock_price->stock_id = $stock->id;
            $stock_price->date = $date;
            $stock_price->price = $price;
            $stock_price->save();
        }
    }
}
