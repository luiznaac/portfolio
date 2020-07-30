<?php

namespace App\Portfolio\Consolidator;

class ConsolidatorCoordinator {

    public static function consolidate(): void {
        $consolidators = self::getConsolidators();

        /** @var ConsolidatorInterface $consolidator */
        foreach ($consolidators as $consolidator) {
            $consolidator::consolidate();
        }
    }

    private static function getConsolidators(): array {
        return [
            StockPositionConsolidator::class,
            StockDividendConsolidator::class,
        ];
    }
}
