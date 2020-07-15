<?php

namespace App\Portfolio\Providers;

use App\Model\Log\Log;
use App\Portfolio\API\Interfaces\HolidayAPI;
use App\Portfolio\API\StatusInvestAPI;
use Carbon\Carbon;

class HolidayProvider {

    private const HOLIDAY_APIS = [
        StatusInvestAPI::class,
    ];

    private const ENTITY_NAME = 'HolidayProvider';

    public static function getHolidaysForYear(Carbon $date): array {
        /** @var HolidayAPI $holiday_api */
        foreach (static::getAvailableAPIs() as $holiday_api) {
            try {
                $holidays = $holiday_api::getHolidaysForYear(clone $date);

                if(empty($holidays)) {
                    continue;
                }

                return $holidays;
            } catch (\Exception $e) {
                Log::log(Log::EXCEPTION_TYPE, self::ENTITY_NAME.'::'.__FUNCTION__, $e->getMessage());
            }
        }

        return [];
    }

    protected static function getAvailableAPIs(): array {
        return self::HOLIDAY_APIS;
    }
}
