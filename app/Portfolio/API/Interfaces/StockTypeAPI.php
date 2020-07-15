<?php

namespace App\Portfolio\API\Interfaces;

use App\Model\Stock\Stock;

interface StockTypeAPI {

    public static function getTypeForStock(Stock $stock): string;
}
