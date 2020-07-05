<?php

namespace App\Portfolio\API;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class UolAPI {

    private const API = 'http://cotacoes.economia.uol.com.br/ws/asset';
    private const LIST_SYMBOL_CODES_ENDPOINT = '/stock/list';
    private const PRICE_ENDPOINT = '/:code/interday?replicate=true&page=1&fields=date,price&begin=:start_date&end=:end_date';

    public static function getStockLastPrice(string $symbol): float {
        $date = Carbon::yesterday();

        return self::getStockPriceForDate($symbol, $date);
    }

    public static function getStockPriceForDate(string $symbol, Carbon $date): float {
        $code = self::getCodeForSymbol($symbol);
        $date = clone $date;
        $tries = 0;

        do {
            $tries++;
            $price = self::getCodePriceForDate($code, $date);
            $date->subDay();
        } while(is_null($price) && $tries < 5);

        return $price;
    }

    private static function getCodeForSymbol(string $symbol): int {
        $symbol_codes = self::getAllSymbolCodes();

        return $symbol_codes[$symbol];
    }

    private static function getCodePriceForDate(int $code, Carbon $date): ?float {
        $endpoint_path = self::buildGetPriceEndpointPath($code, $date);

        $response = Http::get(self::API . $endpoint_path);

        try {
            return $response->json()['data'][0]['price'];
        } catch (\Exception $e) {
            return null;
        }
    }

    protected static function getAllSymbolCodes(): array {
        $response = Http::get(self::API . self::LIST_SYMBOL_CODES_ENDPOINT);

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

    private static function buildGetPriceEndpointPath(int $code, Carbon $date): string {
        [$start_date, $end_date] = self::getStartAndEndDateInUnixMilliseconds($date);
        $price_endpoint = str_replace(':code', $code, self::PRICE_ENDPOINT);
        $price_endpoint = str_replace(':start_date', $start_date, $price_endpoint);
        $price_endpoint = str_replace(':end_date', $end_date, $price_endpoint);

        return $price_endpoint;
    }

    private static function getStartAndEndDateInUnixMilliseconds(Carbon $date): array {
        while($date->isWeekend()) {
            $date = $date->subDay();
        }

        $start_date = $date->startOfDay()->getTimestamp() * 1000;
        $end_date = $date->endOfDay()->getTimestamp() * 1000;

        return [$start_date, $end_date];
    }
}
