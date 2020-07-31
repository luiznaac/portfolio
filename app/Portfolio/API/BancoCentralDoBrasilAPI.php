<?php

namespace App\Portfolio\API;

use App\Model\Index\Index;
use App\Portfolio\API\Interfaces\IndexAPI;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class BancoCentralDoBrasilAPI implements IndexAPI {

    private const API = 'https://api.bcb.gov.br';
    private const INDEX_ENDPOINT = '/dados/serie/bcdata.sgs.:index_code/dados?dataInicial=:start_date&dataFinal=:end_date';

    private const TIMEOUT = 3;

    private const API_INDEX_TYPES = [
        Index::SELIC_INDEX => 11,
        Index::CDI_INDEX => 12,
        Index::IPCA_INDEX => 433,
    ];

    public static function getIndexValuesForRange(Index $index, Carbon $start_date, Carbon $end_date): array {
        $data = self::getIndexValues($index, clone $start_date, clone $end_date);

        return self::buildDateValueArray($data, $start_date, $end_date);
    }

    private static function getIndexValues(Index $index, Carbon $start_date, Carbon $end_date): array {
        $endpoint_path = self::buildGetIndexValueEndpointPath($index, $start_date, $end_date);

        $response = Http::timeout(self::TIMEOUT)->get(self::API . $endpoint_path);

        return $response->json();
    }

    private static function buildGetIndexValueEndpointPath(Index $index, Carbon $start_date, Carbon $end_date) {
        $index_code = self::API_INDEX_TYPES[$index->getAbbr()];

        $endpoint_path = str_replace(':index_code', $index_code, self::INDEX_ENDPOINT);
        $endpoint_path = str_replace(':start_date', $start_date->format('d/m/Y'), $endpoint_path);
        $endpoint_path = str_replace(':end_date', $end_date->format('d/m/Y'), $endpoint_path);

        return $endpoint_path;
    }

    private static function buildDateValueArray(array $data, Carbon $start_date, Carbon $end_date): array {
        $values = [];
        foreach ($data as $date_value) {
            $date = Carbon::createFromFormat('!d/m/Y', $date_value['data']);

            if(!$date->between($start_date, $end_date, true)) {
                continue;
            }

            $values[$date->toDateString()] = $date_value['valor'];
        }

        return $values;
    }
}
