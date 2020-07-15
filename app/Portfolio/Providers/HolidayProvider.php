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
        /** @var HolidayAPI $dividend_api */
        foreach (static::getAvailableAPIs() as $dividend_api) {
            try {
                $dividends = $dividend_api::getHolidaysForYear(clone $date);

                if(empty($dividends)) {
                    continue;
                }

                return $dividends;
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
