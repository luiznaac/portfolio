<?php

namespace App\Portfolio\Providers;

use App\Model\Log\Log;
use App\Model\Stock\Stock;
use App\Portfolio\API\Interfaces\DividendAPI;
use App\Portfolio\API\StatusInvestAPI;
use Carbon\Carbon;

class StockDividendProvider {

    private const DIVIDEND_APIS = [
        StatusInvestAPI::class,
    ];

    private const ENTITY_NAME = 'StockDividendProvider';

    public static function getDividendsForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        /** @var DividendAPI $dividend_api */
        foreach (static::getAvailableAPIs() as $dividend_api) {
            try {
                $dividends = $dividend_api::getDividendsForRange($stock, clone $start_date, clone $end_date);

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
        return self::DIVIDEND_APIS;
    }
}
