<?php

namespace App\Portfolio\API\Interfaces;

use App\Model\Stock\Stock;
use Carbon\Carbon;

interface DividendAPI {

    public static function getDividendsForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array;
}
