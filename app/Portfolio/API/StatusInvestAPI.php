<?php

namespace App\Portfolio\API;

use App\Model\Stock\Stock;
use App\Model\Stock\StockType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class StatusInvestAPI implements PriceAPI, StockTypeAPI, StockExistsAPI {

    private const API = 'https://statusinvest.com.br';
    private const PRICE_ENDPOINT = '/category/tickerprice?ticker=:symbol&type=4';
    private const ETF_PRICE_ENDPOINT = '/etf/tickerprice';
    private const SEARCH_ENDPOINT = '/home/mainsearchquery?q=:text';

    private const TIMEOUT = 2;

    private const API_TYPES = [
        1 => StockType::ACAO_TYPE,
        6 => StockType::ETF_TYPE,
        2 => StockType::FII_TYPE,
    ];

    public static function checkIfSymbolIsValid(string $symbol): bool {
        $response = self::getSearchEndpointResultsForText($symbol);

        return !empty($response) && !(sizeof($response) > 1) && $response[0]['code'] == $symbol;
    }

    public static function getPricesForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        $data = self::getPricesForRangeAccordinglyStockType($stock);

        return self::buildDatePriceArray($data, $start_date, $end_date);
    }

    public static function getPriceForDate(Stock $stock, Carbon $date): float {
        $price_for_date = self::getPricesForRange($stock, $date, $date);

        return $price_for_date[$date->toDateString()];
    }

    public static function getTypeForStock(Stock $stock): string {
        $response = self::getSearchEndpointResultsForText($stock->symbol);

        return self::API_TYPES[$response[0]['type']];
    }

    private static function getPricesForRangeAccordinglyStockType(Stock $stock): array {
        $stock_type = $stock->getStockType();

        if($stock_type->id == StockType::ETF_ID) {
            return self::getETFPrices($stock);
        }

        return self::getStockPrices($stock);
    }

    private static function getETFPrices(Stock $stock): array {
        $parameters = [
            'ticker' => $stock->symbol,
            'type' => 4,
        ];

        $response = Http::timeout(self::TIMEOUT)->get(self::API . self::ETF_PRICE_ENDPOINT, $parameters);
        return $response->json()['prices'];
    }

    private static function getStockPrices(Stock $stock): array {
        $endpoint_path = self::buildGetPriceEndpointPath($stock->symbol);

        $response = Http::timeout(self::TIMEOUT)->get(self::API . $endpoint_path);
        return $response->json()['prices'];
    }

    private static function getSearchEndpointResultsForText(string $text): array {
        $endpoint_path = self::buildSearchEndpointPath($text);
        $response = Http::timeout(self::TIMEOUT)->get(self::API . $endpoint_path);

        return $response->json();
    }

    private static function buildGetPriceEndpointPath(string $symbol): string {
        return str_replace(':symbol', $symbol, self::PRICE_ENDPOINT);
    }

    private static function buildSearchEndpointPath(string $symbol): string {
        return str_replace(':text', $symbol, self::SEARCH_ENDPOINT);
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
