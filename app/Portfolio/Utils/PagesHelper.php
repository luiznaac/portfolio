<?php

namespace App\Portfolio\Utils;

use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\StockPositionConsolidator;

class PagesHelper {

    public static function shouldUpdatePositions(): bool {
        return StockPositionConsolidator::shouldConsolidate();
    }

    public static function update(): void {
        Stock::updateInfosForAllStocks();
        StockPositionConsolidator::consolidate();
    }
}
