<?php

namespace App\Portfolio\Consolidator;

use App\Model\Log\Log;

class ConsolidatorCoordinator {

    public static function consolidate(): void {
        $consolidators = self::getConsolidators();

        /** @var ConsolidatorInterface $consolidator */
        foreach ($consolidators as $consolidator) {
            try {
                $consolidator::consolidate();
            }  catch (\Exception $e) {
                Log::log(Log::EXCEPTION_TYPE, $consolidator, $e->getMessage());
            }
        }
    }

    private static function getConsolidators(): array {
        return [
            StockPositionConsolidator::class,
            StockDividendConsolidator::class,
            BondPositionConsolidator::class,
            TreasuryBondPositionConsolidator::class,
        ];
    }
}
