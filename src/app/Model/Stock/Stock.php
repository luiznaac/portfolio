<?php

namespace App\Model\Stock;

use App\Portfolio\API\AlphaVantageAPI;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Portfolio\Stock
 *
 * @property int $id
 * @property string $symbol
 * @property string $name
 */

class Stock extends Model {

    public function store(string $symbol): void {
        $this->symbol = $symbol;
        $this->name = AlphaVantageAPI::getStockNameForSymbol($symbol);
        $this->save();
    }
}
