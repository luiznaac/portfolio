<?php

namespace App\Portfolio\Providers;

use App\Model\Log\Log;
use App\Model\Stock\Stock;
use App\Portfolio\API\PriceAPI;
use App\Portfolio\API\StatusInvestAPI;
use App\Portfolio\API\UolAPI;
use Carbon\Carbon;

class StockPriceProvider {

    protected const PRICE_APIS = [
        UolAPI::class,
        StatusInvestAPI::class,
    ];

    private const ENTITY_NAME = 'StockPriceProvider';

    public static function getPricesForRange(Stock $stock, Carbon $start_date, Carbon $end_date): ?array {
        /** @var PriceAPI $price_api */
        foreach (static::PRICE_APIS as $price_api) {
            try {
                return $price_api::getPricesForRange($stock, clone $start_date, clone $end_date);
            } catch (\Exception $e) {
                Log::log(Log::EXCEPTION_TYPE, self::ENTITY_NAME.'::'.__FUNCTION__, $e->getMessage());
                continue;
            }
        }

        return null;
    }

    public static function getPriceForDate(Stock $stock, Carbon $date): ?float {
        /** @var PriceAPI $price_api */
        foreach (static::PRICE_APIS as $price_api) {
            try {
                return $price_api::getPriceForDate($stock, clone $date);
            } catch (\Exception $e) {
                Log::log(Log::EXCEPTION_TYPE, self::ENTITY_NAME.'::'.__FUNCTION__, $e->getMessage());
                continue;
            }
        }

        return null;
    }
}
