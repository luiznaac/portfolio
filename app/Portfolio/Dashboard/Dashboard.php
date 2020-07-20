<?php

namespace App\Portfolio\Dashboard;

use App\Model\Stock\Dividend\StockDividendStatementLine;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;

class Dashboard {

    private const PERCENTAGE_PRECISION = 2;
    private const AMOUNT_PRECISION = 2;

    private static $amount_updated;
    private static $amount_contributed;

    public static function getData(): array {
        return [
            'stock_positions_by_type' => self::getStockPositionsAndCalculatePercentages(),
            'amount_updated' => self::$amount_updated ?: 0.0,
            'amount_contributed' => self::$amount_contributed ?: 0.0,
            'overall_variation' => self::calculateOverallVariation() ?: 0.0,
            'dividends_amount' => self::calculateDividendsTotalAmount() ?: 0.0,
        ];
    }

    private static function getStockPositionsAndCalculatePercentages(): array {
        $stock_positions_by_type = self::getLatestStockPositionsByType();
        ksort($stock_positions_by_type);
        [$total_amount,] = self::calculateTotalAmountContributedAndUpdated($stock_positions_by_type);
        $stock_positions_by_type = self::calculatePercentages($stock_positions_by_type, $total_amount);
        $stock_positions_by_type = self::calculateGrossResult($stock_positions_by_type);

        return $stock_positions_by_type;
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

    private static function calculateTotalAmountContributedAndUpdated(array $stock_positions_by_type): array {
        if(isset(self::$amount_updated) && isset(self::$amount_contributed)) {
            return [self::$amount_updated, self::$amount_contributed];
        }

        self::$amount_updated = 0.00;
        self::$amount_contributed = 0.00;
        foreach ($stock_positions_by_type as $stock_type) {
            foreach ($stock_type['positions'] as $stock_position) {
                self::$amount_updated += $stock_position['position']->amount;
                self::$amount_contributed += $stock_position['position']->contributed_amount;
            }
        }

        return [self::$amount_updated, self::$amount_contributed];
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

    private static function calculateGrossResult(array $stock_positions_by_type): array {
        foreach ($stock_positions_by_type as &$stock_type) {
            foreach ($stock_type['positions'] as &$stock_position) {
                /** @var StockPosition $position */
                $position = $stock_position['position'];
                $stock_position['gross_result'] = round($position->amount - $position->contributed_amount, self::AMOUNT_PRECISION);
                $stock_position['gross_result_percentage'] = round(($stock_position['gross_result']/$position->contributed_amount)*100, self::PERCENTAGE_PRECISION);
            }
        }

        return $stock_positions_by_type;
    }

    private static function calculateOverallVariation(): ?float {
        if(!isset(self::$amount_contributed) || self::$amount_contributed == 0.0) {
            return null;
        }

        return round(((self::$amount_updated - self::$amount_contributed)/self::$amount_contributed)*100,
            self::AMOUNT_PRECISION);
    }

    private static function calculateDividendsTotalAmount(): ?float {
        return StockDividendStatementLine::getTotalAmountPaid();
    }
}
