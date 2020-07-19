<?php

namespace App\Portfolio\Utils;

use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\StockConsolidator;

class PagesHelper {

    public static function shouldUpdatePositions(): bool {
        return StockConsolidator::shouldConsolidate();
    }

    public static function update(): void {
        Stock::updateInfosForAllStocks();
        StockConsolidator::consolidate();
    }
}
