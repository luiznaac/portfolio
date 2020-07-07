<?php

namespace App\Model\Stock;

use App\Portfolio\API\AlphaVantageAPI;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Stock\Stock
 *
 * @property int $id
 * @property string $symbol
 * @property string $name
 */

class Stock extends Model {

    protected $fillable = ['symbol'];

    public function store(string $symbol): void {
        $this->symbol = $symbol;
        $this->save();
    }

    public static function getStockBySymbol(string $symbol): ?self {
        return self::where('symbol', $symbol)->get()->first();
    }

    public function getStockPrices(): Collection {
        return StockPrice::where('stock_id', $this->id)->orderBy('date')->get();
    }

    public function getStockPriceForDate(Carbon $date): float {
        $stock_price = StockPrice::query()
            ->where('stock_id', $this->id)
            ->where('date', $date->toDateString())
            ->get()->first();

        if(!$stock_price) {
            $stock_price = $this->loadStockPriceForDate($date);
        }

        return $stock_price->price;
    }

    public function loadStockName(): void {
        $this->name = AlphaVantageAPI::getStockNameForSymbol($this->symbol);
        $this->save();
    }

    public function loadStockPrices(Carbon $start_date, Carbon $end_date): void {
        StockPrice::storePricesForDates($this, $start_date, $end_date);
    }

    private function loadStockPriceForDate(Carbon $date): StockPrice {
        $stock_price = new StockPrice();
        $stock_price->store($this, $date);

        return $stock_price;
    }
}
