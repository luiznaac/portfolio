<?php

namespace App\Portfolio\API;

use App\Model\Stock\Stock;
use App\Model\Stock\StockType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class StatusInvestAPI implements PriceAPI, StockTypeAPI, StockExistsAPI, DividendAPI {

    private const API = 'https://statusinvest.com.br';
    private const PRICE_ENDPOINT = '/category/tickerprice?ticker=:symbol&type=4';
    private const ETF_PRICE_ENDPOINT = '/etf/tickerprice';
    private const SEARCH_ENDPOINT = '/home/mainsearchquery?q=:text';
    private const DIVIDEND_ENDPOINT = '/:stock_type/getearnings?Filter=:symbol&Start=:start_date&End=:end_date';

    private const TIMEOUT = 2;

    private const API_TYPES = [
        1 => StockType::ACAO_TYPE,
        6 => StockType::ETF_TYPE,
        2 => StockType::FII_TYPE,
    ];

    public static function getDividendsForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        $data = self::getDividendsForRangeAccordinglyStockType($stock, $start_date, $end_date);

        return self::buildDividendDataArray($data['datePayment']);
    }

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

    private static function getDividendsForRangeAccordinglyStockType(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        $stock_type = $stock->getStockType();

        switch ($stock_type->type) {
            case StockType::FII_TYPE:
                $type = 'fii';
                break;
            default:
                $type = 'acao';
                break;
        }

        return self::getStockDividends($stock, $type, $start_date, $end_date);
    }

    private static function getStockDividends(Stock $stock, string $type, Carbon $start_date, Carbon $end_date): array {
        $endpoint_path = self::buildGetDividendEndpointPath($stock->symbol, $type, $start_date, $end_date);

        $response = Http::timeout(self::TIMEOUT)->get(self::API . $endpoint_path);

        return $response->json();
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

    private static function buildGetDividendEndpointPath(string $symbol, string $type, Carbon $start_date, Carbon $end_date): string {
        $endpoint_path = str_replace(':stock_type', $type, self::DIVIDEND_ENDPOINT);
        $endpoint_path = str_replace(':symbol', $symbol, $endpoint_path);
        $endpoint_path = str_replace(':start_date', $start_date->toDateString(), $endpoint_path);
        $endpoint_path = str_replace(':end_date', $end_date->toDateString(), $endpoint_path);

        return $endpoint_path;
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

    private static function buildDividendDataArray(array $data): array {
        $dividends_data = [];
        foreach ($data as $dividend) {
            $date_paid = Carbon::createFromFormat('d/m/Y', $dividend['paymentDividend'])->toDateString();
            $reference_date = Carbon::createFromFormat('d/m/Y', $dividend['dateCom'])->toDateString();
            $type = $dividend['earningType'] == 'Rendimento' ? 'Dividendo' : $dividend['earningType'];
            $value = str_replace(',', '.', $dividend['resultAbsoluteValue']);

            $dividends_data["$date_paid|$reference_date|$type"] = round($value, 8);
        }

        return $dividends_data;
    }
}
