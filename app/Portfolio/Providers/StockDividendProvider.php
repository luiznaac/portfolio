<?php

namespace App\Portfolio\Providers;

use App\Model\Log\Log;
use App\Model\Stock\Stock;
use App\Portfolio\API\DividendAPI;
use App\Portfolio\API\StatusInvestAPI;
use Carbon\Carbon;

class StockDividendProvider {

    protected const DIVIDEND_APIS = [
        StatusInvestAPI::class,
    ];

    private const ENTITY_NAME = 'StockDividendProvider';

    public static function getDividendsForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        /** @var DividendAPI $dividend_api */
        foreach (static::DIVIDEND_APIS as $dividend_api) {
            try {
                return $dividend_api::getDividendsForRange($stock, clone $start_date, clone $end_date);
            } catch (\Exception $e) {
                Log::log(Log::EXCEPTION_TYPE, self::ENTITY_NAME.'::'.__FUNCTION__, $e->getMessage());
                continue;
            }
        }

        return [];
    }
}
