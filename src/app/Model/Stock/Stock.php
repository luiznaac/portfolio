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

    public function loadStockInfoForDate(Carbon $date): void {
        $stock_info = new StockInfo();
        $stock_info->store($this, $date);
    }

    public function loadStockName(): void {
        $this->name = AlphaVantageAPI::getStockNameForSymbol($this->symbol);
        $this->save();
    }
}
