<?php

namespace App\Portfolio\API;

use Illuminate\Support\Facades\Http;

class AlphaVantageAPI {

    private const API = 'https://www.alphavantage.co/query?';
    private const KEY = '9LX170KU6457PN56';
    private const SYMBOL_SEARCH_FUNCTION = 'function=SYMBOL_SEARCH&keywords=:keyword&apikey=:api_key';

    public static function getStockNameForSymbol(string $symbol): ?string {
        $best_match = self::getSymbolSearch($symbol);

        return $best_match ? $best_match['2. name'] : null;
    }

    private static function getSymbolSearch(string $symbol): ?array {
        $endpoint_path = self::buildSymbolSearchEndpointPath($symbol);

        $response = Http::get(self::API . $endpoint_path);

        $best_matches = $response->json()['bestMatches'];

        return $best_matches ? $response->json()['bestMatches'][0] : null;
    }

    private static function buildSymbolSearchEndpointPath(string $symbol): string {
        $symbol_search_endpoint = str_replace(':keyword', $symbol, self::SYMBOL_SEARCH_FUNCTION);
        $symbol_search_endpoint = self::addApiKey($symbol_search_endpoint);

        return $symbol_search_endpoint;
    }

    private static function addApiKey(string $path): string {
        return str_replace(':api_key', self::KEY, $path);
    }
}
