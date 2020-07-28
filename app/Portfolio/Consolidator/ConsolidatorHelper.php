<?php

namespace App\Portfolio\Consolidator;

class ConsolidatorHelper {

    public static function shouldConsolidate(): bool {
        return !empty(ConsolidatorDateProvider::getStockPositionDatesToBeUpdated())
            || !empty(ConsolidatorDateProvider::getStockDividendDatesToBeUpdated());
    }
}
