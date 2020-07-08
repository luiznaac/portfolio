<?php

namespace App\Portfolio\API;

use App\Model\Stock\Stock;

interface StockTypeAPI {

    public static function getTypeForStock(Stock $stock): string;
}
