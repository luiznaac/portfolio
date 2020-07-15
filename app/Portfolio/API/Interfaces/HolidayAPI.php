<?php

namespace App\Portfolio\API\Interfaces;

use Carbon\Carbon;

interface HolidayAPI {

    public static function getHolidaysForYear(Carbon $date): array;
}
