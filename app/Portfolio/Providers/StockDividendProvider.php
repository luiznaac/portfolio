<?php

namespace App\Portfolio\Providers;

use App\Model\Stock\Stock;
use App\Portfolio\API\DividendAPI;
use App\Portfolio\API\StatusInvestAPI;
use Carbon\Carbon;

class StockDividendProvider {

    private const DIVIDEND_APIS = [
        StatusInvestAPI::class,
    ];

    public static function getDividendsForRange(Stock $stock, Carbon $start_date, Carbon $end_date): ?array {
        /** @var DividendAPI $dividend_api */
        foreach (self::DIVIDEND_APIS as $dividend_api) {
            try {
                return $dividend_api::getDividendsForRange($stock, clone $start_date, clone $end_date);
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }
}
