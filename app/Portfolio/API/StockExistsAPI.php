<?php

namespace App\Portfolio\API;

use App\Model\Stock\Stock;
use Carbon\Carbon;

interface StockExistsAPI {

    public static function checkIfSymbolIsValid(string $symbol): bool;
}
