<?php

namespace App\Portfolio\Utils;

use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\ConsolidatorCoordinator;
use App\Portfolio\Consolidator\ConsolidatorHelper;
use App\Portfolio\Consolidator\StockPositionConsolidator;

class PagesHelper {

    public static function shouldUpdatePositions(): bool {
        return ConsolidatorHelper::shouldConsolidate();
    }

    public static function update(): void {
        Stock::updateInfosForAllStocks();
        ConsolidatorCoordinator::consolidate();
    }
}
