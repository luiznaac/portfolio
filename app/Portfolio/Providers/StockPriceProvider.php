<?php

namespace App\Portfolio\Providers;

use App\Model\Stock\Stock;
use App\Portfolio\API\PriceAPI;
use App\Portfolio\API\StatusInvestAPI;
use App\Portfolio\API\UolAPI;
use Carbon\Carbon;

class StockPriceProvider {

    private const PRICE_APIS = [
        UolAPI::class,
        StatusInvestAPI::class,
    ];

    public static function getPricesForRange(Stock $stock, Carbon $start_date, Carbon $end_date): ?array {
        /** @var PriceAPI $price_api */
        foreach (self::PRICE_APIS as $price_api) {
            try {
                return $price_api::getPricesForRange($stock, clone $start_date, clone $end_date);
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    public static function getPriceForDate(Stock $stock, Carbon $date): ?float {
        /** @var PriceAPI $price_api */
        foreach (self::PRICE_APIS as $price_api) {
            try {
                return $price_api::getPriceForDate($stock, clone $date);
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }
}
