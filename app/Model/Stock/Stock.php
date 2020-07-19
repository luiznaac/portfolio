<?php

namespace App\Model\Stock;

use App\Model\Stock\Dividend\StockDividend;
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

    public static function isValidSymbol(string $symbol): bool {
        $stock = self::where('symbol', $symbol)->get()->first();

        if($stock) {
            return true;
        }

        return StatusInvestAPI::checkIfSymbolIsValid($symbol);
    }

    public static function getStockBySymbol(string $symbol): self {
        return Stock::firstOrCreate(['symbol' => $symbol]);
    }

    public function getStockDividends(): Collection {
        return StockDividend::where('stock_id', $this->id)->orderBy('date_paid')->get();
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
            $stock_price = StockPrice::loadPriceForDateAndStore($this, $date);
        }

        return $stock_price ? $stock_price->price : null;
    }

    public function getStockType(): StockType {
        if(!isset($this->stock_type_id)) {
            $this->loadStockType();
        }

        return StockType::find($this->stock_type_id ?: StockType::ACAO_ID);
    }

    public function getStockName(): ?string {
        if(!isset($this->name)) {
            $this->loadStockName();
        }

        return $this->name;
    }

    public static function getAllStocksFromCache(): array {
        $stocks = self::query()->get();

        $stocks_cache = [];
        /** @var Stock $stock */
        foreach ($stocks as $stock) {
            $stocks_cache[$stock->id] = $stock;
        }

        return $stocks_cache;
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

    private function loadStockName(): void {
        try {
            $this->name = AlphaVantageAPI::getStockNameForSymbol($this->symbol);
            $this->save();
        }  catch (\Exception $e) {
            return;
        }
    }

    private function loadStockType(): void {
        try {
            $type = StatusInvestAPI::getTypeForStock($this);
            $stock_type = StockType::getStockTypeByType($type);
            $this->stock_type_id = $stock_type->id;
            $this->save();
        } catch (\Exception $e) {
            return;
        }
    }
}
