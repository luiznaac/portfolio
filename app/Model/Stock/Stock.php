<?php

namespace App\Model\Stock;

use App\Portfolio\API\AlphaVantageAPI;
use App\Portfolio\API\StatusInvestAPI;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Stock\Stock
 *
 * @property int $id
 * @property string $symbol
 * @property int $stock_type_id
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

    public function getStockPriceForDate(Carbon $date): ?float {
        $stock_price = StockPrice::query()
            ->where('stock_id', $this->id)
            ->where('date', $date->toDateString())
            ->get()->first();

        if(!$stock_price) {
            $stock_price = StockPrice::store($this, $date);
        }

        return $stock_price ? $stock_price->price : null;
    }

    public function getStockType(): StockType {
        if(!isset($this->stock_type_id)) {
            return $this->loadStockType();
        }

        return StockType::find($this->stock_type_id);
    }

    public static function updateInfosForAllStocks(): void {
        $stocks = self::all();

        /** @var Stock $stock */
        foreach ($stocks as $stock) {
            if(!isset($stock->name)) {
                $stock->loadStockName();
            }

            if(!isset($stock->stock_type_id)) {
                $stock->loadStockType();
            }
        }
    }

    public function loadStockName(): void {
        $this->name = AlphaVantageAPI::getStockNameForSymbol($this->symbol);
        $this->save();
    }

    public function loadStockPrices(Carbon $start_date, Carbon $end_date): void {
        StockPrice::storePricesForDates($this, $start_date, $end_date);
    }

    private function loadStockType(): void {
        $type = StatusInvestAPI::getTypeForStock($this);
        $stock_type = StockType::getStockTypeByType($type);
        $this->stock_type_id = $stock_type->id;
        $this->save();
    }
}
