<?php

namespace App\Portfolio\API;

use App\Model\Stock\Stock;
use App\Portfolio\API\Interfaces\PriceAPI;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class UolAPI implements PriceAPI {

    private const API = 'http://cotacoes.economia.uol.com.br/ws/asset';
    private const LIST_SYMBOL_CODES_ENDPOINT = '/stock/list';
    private const PRICE_ENDPOINT = '/:code/interday?replicate=true&page=1&fields=date,price&begin=:start_date&end=:end_date';

    private const TIMEOUT = 2;

    public static function getPricesForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        $code = self::getCodeForSymbol($stock->symbol);

        return self::getCodePriceForDateRange($code, $start_date, $end_date);
    }

    public static function getPriceForDate(Stock $stock, Carbon $date): float {
        $price_for_date = self::getPricesForRange($stock, $date, $date);

        return $price_for_date[$date->toDateString()];
    }

    private static function getCodeForSymbol(string $symbol): int {
        $symbol_codes = self::getAllSymbolCodes();

        return $symbol_codes[$symbol];
    }

    private static function getCodePriceForDateRange(int $code, Carbon $start_date, Carbon $end_date): array {
        $endpoint_path = self::buildGetPriceEndpointPath($code, $start_date, $end_date);

        $response = Http::timeout(self::TIMEOUT)->get(self::API . $endpoint_path);
        $data = $response->json()['data'];
        $expected_dates = Calendar::getWorkingDaysDatesForRange($start_date, $end_date);

        return self::buildDatePriceArray($data, $expected_dates);
    }

    protected static function getAllSymbolCodes(): array {
        $response = Http::timeout(self::TIMEOUT)->get(self::API . self::LIST_SYMBOL_CODES_ENDPOINT);

        return self::buildSymbolCodeArray($response->json()['data']);
    }

    private static function buildSymbolCodeArray(array $data_array): array {
        $symbol_code = [];
        foreach ($data_array as $data) {
            $symbol = substr($data['code'], 0, strpos($data['code'], '.'));
            $code = $data['idt'];

            $symbol_code[$symbol] = $code;
        }

        return $symbol_code;
    }

    private static function buildGetPriceEndpointPath(int $code, Carbon $start_date, Carbon $end_date = null): string {
        [$start_date, $end_date] = self::getStartAndEndDateInUnixMilliseconds($start_date, $end_date);
        $price_endpoint = str_replace(':code', $code, self::PRICE_ENDPOINT);
        $price_endpoint = str_replace(':start_date', $start_date, $price_endpoint);
        $price_endpoint = str_replace(':end_date', $end_date, $price_endpoint);

        return $price_endpoint;
    }

    private static function getStartAndEndDateInUnixMilliseconds(Carbon $start_date, ?Carbon $end_date): array {
        while(!isset($end_date) && $start_date->isWeekend()) {
            $start_date = $start_date->subDay();
        }

        $start_date_unix = $start_date->startOfDay()->getTimestamp() * 1000;
        $end_date_unix = ($end_date ?: $start_date)->endOfDay()->getTimestamp() * 1000;

        return [$start_date_unix, $end_date_unix];
    }

    private static function buildDatePriceArray(array $data, array $expected_dates): array {
        $prices = [];
        foreach ($data as $date_price) {
            $date = Carbon::createFromTimestampMs($date_price['date'])->toDateString();

            if(!in_array($date, $expected_dates)) {
                continue;
            }

            $price = $date_price['price'];

            $prices[$date] = $price;
        }

        return $prices;
    }
}
