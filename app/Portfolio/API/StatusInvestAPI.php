<?php

namespace App\Portfolio\API;

use App\Model\Stock\Stock;
use App\Model\Stock\StockType;
use App\Portfolio\API\Interfaces\DividendAPI;
use App\Portfolio\API\Interfaces\HolidayAPI;
use App\Portfolio\API\Interfaces\PriceAPI;
use App\Portfolio\API\Interfaces\StockExistsAPI;
use App\Portfolio\API\Interfaces\StockTypeAPI;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class StatusInvestAPI implements PriceAPI, StockTypeAPI, StockExistsAPI, DividendAPI, HolidayAPI {

    private const API = 'https://statusinvest.com.br';
    private const PRICE_ENDPOINT = '/:type/tickerpricerange?ticker=:symbol&start=:start_date&end=:end_date';
    private const SEARCH_ENDPOINT = '/home/mainsearchquery?q=:text';
    private const DIVIDEND_ENDPOINT = '/:stock_type/getearnings?Filter=:symbol&Start=:start_date&End=:end_date';
    private const HOLIDAY_ENDPOINT = '/calendar/getevents?type=99&year=:year&month=:month';

    private const TIMEOUT = 3;
    private const RETRIES_FOR_HOLIDAY = 2;

    private const API_TYPES = [
        1 => StockType::ACAO_TYPE,
        6 => StockType::ETF_TYPE,
        2 => StockType::FII_TYPE,
    ];

    public static function getHolidaysForYear(Carbon $date): array {
        $data = self::getHolidays($date);

        return self::buildHolidayDataArray($data);
    }

    public static function getDividendsForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        $data = self::getDividendsForRangeAccordinglyStockType($stock, $start_date, $end_date);

        return self::buildDividendDataArray($data['datePayment']);
    }

    public static function checkIfSymbolIsValid(string $symbol): bool {
        $response = self::getSearchEndpointResultsForText($symbol);

        return !empty($response) && !(sizeof($response) > 1) && $response[0]['code'] == $symbol;
    }

    public static function getPricesForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        $data = self::getPricesForRangeAccordinglyStockType($stock, $start_date, $end_date);

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

    private static function getPricesForRangeAccordinglyStockType(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        $stock_type = $stock->getStockType();

        switch ($stock_type->type) {
            case StockType::ETF_TYPE:
                $type = 'etf';
                break;
            case StockType::ACAO_TYPE:
                $type = 'acao';
                break;
            case StockType::FII_TYPE:
                $type = 'fii';
                break;
        }

        return self::getPrices($stock, $type, $start_date, $end_date);
    }

    private static function getPrices(Stock $stock, string $type, Carbon $start_date, Carbon $end_date): array {
        $endpoint_path = self::buildGetPriceEndpointPath($stock->symbol, $type, $start_date, $end_date);

        $response = Http::timeout(self::TIMEOUT)
            ->withoutVerifying()
            ->get(self::API . $endpoint_path);
        return $response->json()['data'][0]['prices'];
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

        $response = Http::timeout(self::TIMEOUT)
            ->withoutVerifying()
            ->get(self::API . $endpoint_path);

        return $response->json();
    }

    private static function getSearchEndpointResultsForText(string $text): array {
        $endpoint_path = self::buildSearchEndpointPath($text);
        $response = Http::timeout(self::TIMEOUT)
            ->withoutVerifying()
            ->get(self::API . $endpoint_path);

        return $response->json();
    }

    private static function getHolidays(Carbon $year_date): array {
        $date = (clone $year_date)->startOfYear();
        $end_of_year = (clone $year_date)->endOfYear();

        $data = [];
        while($date->lte($end_of_year)) {
            $endpoint_path = self::buildHolidayEndpointPath($date->year, $date->month);
            $holidays_in_month = self::makeRequestAndParseResultForHolidayEndpoint($endpoint_path);
            $data = array_merge($data, $holidays_in_month);

            $date->addMonth();
        }

        return $data;
    }

    private static function makeRequestAndParseResultForHolidayEndpoint(string $endpoint_path): array {
        $times_tried = 0;

        while($times_tried < self::RETRIES_FOR_HOLIDAY) {
            $times_tried += 1;

            try {
                $response = Http::timeout(self::TIMEOUT)
                    ->withoutVerifying()
                    ->get(self::API . $endpoint_path);
                $data = $response->json();

                if ($data['totalEvents'] > 0) {
                    return $data['holidays'];
                }

                return [];
            } catch(\Exception $exception) {

            }
        }

        throw new \Exception("Something went wrong when trying to get holidays. Endpoint: $endpoint_path");
    }

    private static function buildHolidayEndpointPath(int $year, int $month): string {
        $endpoint_path = str_replace(':year', $year, self::HOLIDAY_ENDPOINT);
        $endpoint_path = str_replace(':month', $month, $endpoint_path);

        return $endpoint_path;
    }

    private static function buildGetPriceEndpointPath(string $symbol, string $type, Carbon $start_date, Carbon $end_date): string {
        $endpoint_path = str_replace(':type', $type, self::PRICE_ENDPOINT);
        $endpoint_path = str_replace(':symbol', $symbol, $endpoint_path);
        $endpoint_path = str_replace(':start_date', $start_date->toDateString(), $endpoint_path);
        $endpoint_path = str_replace(':end_date', $end_date->toDateString(), $endpoint_path);

        return $endpoint_path;
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
            $date_paid = Carbon::createFromFormat('d/m/Y', $dividend['paymentDividend']);
            $reference_date = Carbon::createFromFormat('d/m/Y', $dividend['dateCom']);
            $type = $dividend['earningType'] == 'Rendimento' ? 'Dividendo' : $dividend['earningType'];
            $value = str_replace(',', '.', $dividend['resultAbsoluteValue']);

            if($reference_date->gt($date_paid)) {
                continue;
            }

            $date_paid = $date_paid->toDateString();
            $reference_date = $reference_date->toDateString();

            $dividends_data["$date_paid|$reference_date|$type"] = round($value, 8);
        }

        return $dividends_data;
    }

    private static function buildHolidayDataArray(array $data): array {
        $holidays_data = [];
        foreach ($data as $holiday) {
            $date = Carbon::createFromFormat('d/m/Y', $holiday['date']);
            $description = $holiday['description'];

            $holidays_data[$date->toDateString()] = $description;
        }

        return $holidays_data;
    }
}
