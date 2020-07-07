<?php

namespace App\Portfolio\Utils;

use Carbon\Carbon;

class Calendar {

    public static function getLastWorkingDayForDate(Carbon $date): Carbon {
        $date = clone $date;

        while($date->isWeekend()) {
            $date->subDay();
        }

        return $date;
    }

    public static function getWorkingDaysDatesForRange(Carbon $start_date, Carbon $end_date = null): array {
        $date = clone $start_date;
        $end_date = $end_date ?: Carbon::today();

        $working_dates = [];
        while($date->lte($end_date)) {
            while($date->isWeekend() && $date->lt($end_date)) {
                $date->addDay();
            }

            if(!$date->isWeekend()) {
                $working_dates[] = $date->toDateString();
            }

            $date->addDay();
        }

        return $working_dates;
    }
}
