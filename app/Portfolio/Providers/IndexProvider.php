<?php

namespace App\Portfolio\Providers;

use App\Model\Index\Index;
use App\Model\Log\Log;
use App\Portfolio\API\BancoCentralDoBrasilAPI;
use App\Portfolio\API\Interfaces\IndexAPI;
use Carbon\Carbon;

class IndexProvider {

    private const INDEX_APIS = [
        BancoCentralDoBrasilAPI::class,
    ];

    private const ENTITY_NAME = 'IndexProvider';

    public static function getIndexValuesForRange(Index $index, Carbon $start_date, Carbon $end_date): array {
        /** @var IndexAPI $index_api */
        foreach (static::getAvailableAPIs() as $index_api) {
            try {
                $values = $index_api::getIndexValuesForRange($index, clone $start_date, clone $end_date);

                if(empty($values)) {
                    continue;
                }

                return $values;
            } catch (\Exception $e) {
                Log::log(Log::EXCEPTION_TYPE, self::ENTITY_NAME.'::'.__FUNCTION__, $e->getMessage());
            }
        }

        return [];
    }

    protected static function getAvailableAPIs(): array {
        return self::INDEX_APIS;
    }
}
