<?php

namespace App\Portfolio\Utils;

use App\Model\Holiday\Holiday;
use Carbon\Carbon;

class Calendar {

    public const B3_TIMEZONE = 'America/Sao_Paulo';

    public static function getLastMarketWorkingDate(): Carbon {
        $now_in_brazil = Carbon::now()->setTimezone(self::B3_TIMEZONE);

        if($now_in_brazil->format('H') < 18) {
            return self::getLastWorkingDayForDate(Carbon::yesterday());
        }

        return self::getLastWorkingDay();
    }

    public static function getLastWorkingDay(): Carbon {
        $today_in_brazil = Carbon::parse(Carbon::now()->setTimezone(self::B3_TIMEZONE)->toDateString());

        return self::getLastWorkingDayForDate($today_in_brazil);
    }

    public static function getLastWorkingDayForDate(Carbon $date): Carbon {
        $date = clone $date;

        while(!self::isWorkingDay($date)) {
            $date->subDay();
        }

        return $date;
    }

    public static function getWorkingDaysDatesForRange(Carbon $start_date, Carbon $end_date = null): array {
        $date = clone $start_date;
        $end_date = $end_date ?: Carbon::today();

        $working_dates = [];
        while($date->lte($end_date)) {
            while(!self::isWorkingDay($date) && $date->lt($end_date)) {
                $date->addDay();
            }

            if(self::isWorkingDay($date)) {
                $working_dates[] = $date->toDateString();
            }

            $date->addDay();
        }

        return $working_dates;
    }

    public static function isWorkingDay(Carbon $date): bool {
        return !$date->isWeekend() && !Holiday::isHoliday($date);
    }

    public static function getYearsForRange(Carbon $start_date, Carbon $end_date): array {
        $date = (clone $start_date)->startOfYear();

        $years = [];
        while ($date->lte($end_date)) {
            $years[] = $date->year;
            $date->addYear();
        }

        return $years;
    }

    public static function getStartOfAllMonthsForRange(Carbon $start_date, Carbon $end_date): array {
        $date = clone $start_date;

        $dates = [];
        while ($date->lte($end_date)) {
            $dates[] = $date->startOfMonth()->toDateString();
            $date->addMonth();
        }

        return $dates;
    }
}
