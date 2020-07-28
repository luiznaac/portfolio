<?php

namespace App\Portfolio\API\Interfaces;

use App\Model\Index\Index;
use Carbon\Carbon;

interface IndexAPI {

    public static function getIndexValuesForRange(Index $index, Carbon $start_date, Carbon $end_date): array;
}
