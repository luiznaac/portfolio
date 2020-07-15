<?php

namespace App\Portfolio\API\Interfaces;

use App\Model\Stock\Stock;
use Carbon\Carbon;

interface PriceAPI {

    public static function getPricesForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array;

    public static function getPriceForDate(Stock $stock, Carbon $date): float;
}
