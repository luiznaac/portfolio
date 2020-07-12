<?php

namespace App\Portfolio\Dashboard;

use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;

class Dashboard {

    private const PERCENTAGE_PRECISION = 2;

    public static function getData(): array {
        return [
            'stock_positions_by_type' => self::getStockPositionsAndCalculatePercentages(),
        ];
    }

    private static function getStockPositionsAndCalculatePercentages(): array {
        $stock_positions_by_type = self::getLatestStockPositionsByType();
        ksort($stock_positions_by_type);
        $total_amount = self::calculateTotalAmount($stock_positions_by_type);

        return self::calculatePercentages($stock_positions_by_type, $total_amount);
    }

    private static function getLatestStockPositionsByType(): array {
        $stock_positions = StockPosition::getLastStockPositions();

        $positions_by_type = [];
        /** @var StockPosition $stock_position */
        foreach ($stock_positions as $stock_position) {
            $stock_type_id = Stock::find($stock_position->stock_id)->stock_type_id;
            $positions_by_type[$stock_type_id]['positions'][] = [
                'position' => $stock_position,
            ];
        }

        return $positions_by_type;
    }

    private static function calculateTotalAmount(array $stock_positions_by_type): float {
        $amount = 0.00;
        foreach ($stock_positions_by_type as $stock_type) {
            foreach ($stock_type['positions'] as $stock_position) {
                $amount += $stock_position['position']->amount;
            }
        }

        return $amount;
    }

    private static function calculatePercentages(array $stock_positions_by_type, float $total_amount): array {
        foreach ($stock_positions_by_type as &$stock_type) {
            foreach ($stock_type['positions'] as &$stock_position) {
                $stock_position['percentage'] =
                    round(($stock_position['position']->amount/$total_amount)*100, self::PERCENTAGE_PRECISION);
            }
            usort($stock_type['positions'], function ($position_1, $position_2) {
                return $position_1['percentage'] < $position_2['percentage'];
            });
        }

        foreach ($stock_positions_by_type as &$stock_type) {
            $stock_type['percentage'] =
                round(array_sum(array_column($stock_type['positions'], 'percentage')), self::PERCENTAGE_PRECISION);
        }

        return $stock_positions_by_type;
    }
}
