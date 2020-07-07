<?php

namespace App\Portfolio\API;

use App\Model\Stock\Stock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class StatusInvestAPI implements PriceAPI {

    private const API = 'https://statusinvest.com.br';
    private const PRICE_ENDPOINT = '/category/tickerprice?ticker=:symbol&type=4';

    public static function getPricesForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        $endpoint_path = self::buildGetPriceEndpointPath($stock->symbol);

        $response = Http::get(self::API . $endpoint_path);
        $data = $response->json()['prices'];

        return self::buildDatePriceArray($data, $start_date, $end_date);
    }

    public static function getPriceForDate(Stock $stock, Carbon $date): float {
        $price_for_date = self::getPricesForRange($stock, $date, $date);

        return $price_for_date[$date->toDateString()];
    }

    private static function buildGetPriceEndpointPath(string $symbol): string {
        return str_replace(':symbol', $symbol, self::PRICE_ENDPOINT);
    }

    private static function buildDatePriceArray(array $data, Carbon $start_date, Carbon $end_date): array {
        $prices = [];
        foreach ($data as $date_price) {
            $date = Carbon::createFromFormat('d/m/y H:i', $date_price['date']);

            if(!$date->between($start_date, $end_date, true)) {
                continue;
            }

            $prices[$date->toDateString()] = $date_price['price'];
        }

        return $prices;
    }
}
