<?php

namespace App\Portfolio\API;

use App\Model\Stock\Stock;
use App\Model\Stock\StockType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class StatusInvestAPI implements PriceAPI, StockTypeAPI {

    private const API = 'https://statusinvest.com.br';
    private const PRICE_ENDPOINT = '/category/tickerprice?ticker=:symbol&type=4';
    private const SEARCH_ENDPOINT = '/home/mainsearchquery?q=:symbol';

    private const API_TYPES = [
        1 => StockType::ACAO_TYPE,
        6 => StockType::ETF_TYPE,
        2 => StockType::FII_TYPE,
    ];

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

    public static function getTypeForStock(Stock $stock): string {
        $endpoint_path = self::buildSearchEndpointPath($stock->symbol);

        $response = Http::get(self::API . $endpoint_path);
        $api_type = $response->json()[0]['type'];

        return self::API_TYPES[$api_type];
    }

    private static function buildGetPriceEndpointPath(string $symbol): string {
        return str_replace(':symbol', $symbol, self::PRICE_ENDPOINT);
    }

    private static function buildSearchEndpointPath(string $symbol): string {
        return str_replace(':symbol', $symbol, self::SEARCH_ENDPOINT);
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
