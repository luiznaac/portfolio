<?php

namespace App\Portfolio\Utils;

use App\Model\Index\Index;

class ReturnRate {

    public static function getReturnRateString(?int $index_id, ?float $index_rate, ?float $interest_rate): string {
        $return_rate = null;

        if ($index_id) {
            $index_abbr = Index::getIndexAbbr($index_id);
            $return_rate = $index_rate . '% ' . $index_abbr;
        }

        if ($interest_rate) {
            $return_rate = ($return_rate ? ($return_rate . ' + ') : '') . $interest_rate . '%';
        }

        return $return_rate ?? '0%';
    }
}
