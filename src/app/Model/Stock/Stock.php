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

    public function getStockInfos(): Collection {
        return StockInfo::where('stock_id', $this->id)->orderBy('date')->get();
    }

    public function getStockPriceForDate(Carbon $date): float {
        $stock_info = StockInfo::query()
            ->where('stock_id', $this->id)
            ->where('date', $date->toDateString())
            ->get()->first();

        if(!$stock_info) {
            $stock_info = $this->loadStockInfoForDate($date);
        }

        return $stock_info->price;
    }

    public function loadStockName(): void {
        $this->name = AlphaVantageAPI::getStockNameForSymbol($this->symbol);
        $this->save();
    }

    public function loadStockPrices(Carbon $start_date, Carbon $end_date): void {
        StockInfo::storePricesForDates($this, $start_date, $end_date);
    }

    private function loadStockInfoForDate(Carbon $date): StockInfo {
        $stock_info = new StockInfo();
        $stock_info->store($this, $date);

        return $stock_info;
    }
}
