<?php

namespace App\Portfolio\Dashboard;

use App\Model\Bond\Bond;
use App\Model\Bond\BondOrder;
use App\Model\Bond\BondPosition;
use App\Model\Bond\BondType;
use App\Model\Bond\Treasury\TreasuryBond;
use App\Model\Bond\Treasury\TreasuryBondPosition;
use App\Model\Stock\Dividend\StockDividendStatementLine;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Model\Stock\StockProfit;
use App\Portfolio\Utils\ReturnRate;

class Dashboard {

    private const PERCENTAGE_PRECISION = 2;
    private const AMOUNT_PRECISION = 2;

    public static function getData(): array {
        $last_stock_positions = StockPosition::getLastStockPositions($validate_quantity = true);
        $last_bond_positions = self::attachBondIdToBondPositions(BondPosition::getLastBondPositions());
        $last_treasury_bond_positions = TreasuryBondPosition::getLastTreasuryBondPositions();

        $contributed_amount = self::calculateContributedAmount(array_merge($last_stock_positions, $last_bond_positions, $last_treasury_bond_positions));
        $updated_amount = self::calculateUpdatedAmount(array_merge($last_stock_positions, $last_bond_positions, $last_treasury_bond_positions));
        $dividends_amount = self::calculateDividendsAmount();
        $overall_variation = self::calculateOverallVariation($contributed_amount, $updated_amount);
        $profit = self::calculateProfit();

        [$stock_allocation, $bond_allocation] = self::calculateAllocations($last_stock_positions, array_merge($last_bond_positions, $last_treasury_bond_positions));
        $stock_type_allocations = self::calculateStockTypeAllocations($last_stock_positions);
        $stock_allocations = self::calculateStockAllocations($last_stock_positions);
        $bond_allocations = self::calculateBondAllocations($last_bond_positions, $last_treasury_bond_positions);

        $stock_positions_list = self::buildStockPositionsList($last_stock_positions);
        $bond_positions_list = self::buildBondPositionsList($last_bond_positions, $last_treasury_bond_positions);

        return [
            'contributed_amount' => $contributed_amount,
            'updated_amount' => $updated_amount,
            'dividends_amount' => $dividends_amount,
            'overall_variation' => $overall_variation,
            'profit' => $profit,
            'stock_allocation' => $stock_allocation,
            'bond_allocation' => $bond_allocation,
            'stock_type_allocations' => $stock_type_allocations,
            'stock_allocations' => $stock_allocations,
            'bond_allocations' => $bond_allocations,
            'stock_positions_list' => $stock_positions_list,
            'bond_positions_list' => $bond_positions_list,
        ];
    }

    private static function attachBondIdToBondPositions(array $bond_positions): array {
        /** @var BondPosition $bond_position */
        foreach ($bond_positions as &$bond_position) {
            /** @var BondOrder $bond_order */
            $bond_order = BondOrder::find($bond_position->bond_order_id);
            $bond_position['bond_id'] = $bond_order->bond_id;
        }

        return $bond_positions;
    }

    private static function calculateContributedAmount(array $positions): float {
        return array_reduce($positions, function ($amount, $item) {
            $amount += $item['contributed_amount'];
            return $amount;
        }) ?? 0.0;
    }

    private static function calculateUpdatedAmount(array $positions): float {
        return array_reduce($positions, function ($amount, $item) {
            $amount += $item['amount'];
            return $amount;
        }) ?? 0.0;
    }

    private static function calculateAllocations(array $stock_positions, array $bond_positions): array {
        $stock_amount = self::calculateUpdatedAmount($stock_positions);
        $bond_amount = self::calculateUpdatedAmount($bond_positions);

        $stock_allocation = 0.0;
        $bond_allocation = 0.0;

        if($stock_amount != 0.0 || $bond_amount != 0.0) {
            $total_amount = $stock_amount + $bond_amount;
            $stock_allocation = round(($stock_amount/$total_amount)*100, self::PERCENTAGE_PRECISION);
            $bond_allocation = round(($bond_amount/$total_amount)*100, self::PERCENTAGE_PRECISION);
        }

        return [$stock_allocation, $bond_allocation];
    }

    private static function calculateStockTypeAllocations(array $stock_positions): array {
        $type_allocations = [];
        foreach ($stock_positions as $stock_position) {
            $stock_type_id = Stock::find($stock_position->stock_id)->stock_type_id;
            $type_allocations[$stock_type_id] = ($type_allocations[$stock_type_id] ?? 0.0) + $stock_position['amount'];
        }

        $stock_amount = self::calculateUpdatedAmount($stock_positions);

        foreach ($type_allocations as &$allocation) {
            $allocation = round(($allocation/$stock_amount)*100, self::PERCENTAGE_PRECISION);
        }

        return $type_allocations;
    }

    private static function calculateStockAllocations(array $stock_positions): array {
        $stock_amount = self::calculateUpdatedAmount($stock_positions);

        $stock_allocations = [];
        foreach ($stock_positions as $position) {
            $allocation = round(($position['amount']/$stock_amount)*100, self::PERCENTAGE_PRECISION);
            $stock_allocations[$position['stock_id']] = $allocation;
        }

        return $stock_allocations;
    }

    private static function calculateBondAllocations(array $bond_positions, array $treasury_bond_positions): array {
        $bond_amount = self::calculateUpdatedAmount(array_merge($bond_positions, $treasury_bond_positions));

        $allocations = [];
        foreach ($bond_positions as $position) {
            $allocation = round(($position['amount']/$bond_amount)*100, self::PERCENTAGE_PRECISION);
            $allocations['bonds'][$position['bond_id']] = $allocation;
        }
        foreach ($treasury_bond_positions as $position) {
            $allocation = round(($position['amount']/$bond_amount)*100, self::PERCENTAGE_PRECISION);
            $allocations['treasury_bonds'][$position['treasury_bond_id']] = $allocation;
        }

        return $allocations;
    }

    private static function calculateOverallVariation(float $contributed_amount, float $updated_amount): float {
        if($contributed_amount == 0.0) {
            return 0.0;
        }

        return round((($updated_amount - $contributed_amount)/$contributed_amount)*100, self::PERCENTAGE_PRECISION);
    }

    private static function calculateProfit(): float {
        $profit = StockProfit::getBaseQuery()->sum('amount');

        return $profit;
    }

    private static function calculateDividendsAmount(): float {
        return StockDividendStatementLine::getTotalAmountPaid() ?? 0.0;
    }

    private static function buildStockPositionsList(array $stock_positions): array {
        $stocks = Stock::getAllStocksFromCache();

        $list = [];
        foreach ($stock_positions as $stock_position) {
            $stock_type = $stocks[$stock_position['stock_id']]['stock_type_id'];
            $stock_position['symbol'] = $stocks[$stock_position['stock_id']]['symbol'];
            $stock_position['result'] = self::calculatePositionResult($stock_position->toArray());
            $stock_position['variation'] = self::calculatePositionVariation($stock_position->toArray());
            $list[$stock_type][] = $stock_position;
        }

        foreach ($list as $type => &$positions) {
            usort($positions, function ($position_1, $position_2) {
                return $position_1['amount'] < $position_2['amount'];
            });
        }
        ksort($list);

        return $list;
    }

    private static function buildBondPositionsList(array $bond_positions, array $treasury_bond_positions): array {
        $bonds = Bond::getAllBondsFromCache();
        $treasury_bonds = TreasuryBond::getAllTreasuryBondsFromCache();

        $list = [];
        foreach ($bond_positions as $bond_position) {
            $bond_type = $bonds[$bond_position['bond_id']]['bond_type_id'];
            $bond = $bonds[$bond_position['bond_id']];
            $return_rate = ReturnRate::getReturnRateString($bond['index_id'], $bond['index_rate'], $bond['interest_rate']);
            $bond_position['bond_name'] = $bond->getBondName() . ' - ' . $return_rate;
            $bond_position['result'] = self::calculatePositionResult($bond_position->toArray());
            $bond_position['variation'] = self::calculatePositionVariation($bond_position->toArray());
            $list[$bond_type][] = $bond_position;
        }

        foreach ($treasury_bond_positions as $treasury_bond_position) {
            $bond_type = BondType::TESOURO_DIRETO_ID;
            $treasury_bond_position['bond_name'] = $treasury_bonds[$treasury_bond_position['treasury_bond_id']]->getTreasuryBondName();
            $treasury_bond_position['result'] = self::calculatePositionResult($treasury_bond_position->toArray());
            $treasury_bond_position['variation'] = self::calculatePositionVariation($treasury_bond_position->toArray());
            $list[$bond_type][] = $treasury_bond_position;
        }

        foreach ($list as $type => &$positions) {
            usort($positions, function ($position_1, $position_2) {
                return $position_1['amount'] < $position_2['amount'];
            });
        }

        return $list;
    }

    private static function calculatePositionResult(array $position): float {
        return round($position['amount'] - $position['contributed_amount'], self::AMOUNT_PRECISION);
    }

    private static function calculatePositionVariation(array $position): float {
        return round((($position['amount'] - $position['contributed_amount'])/$position['contributed_amount'])*100, self::AMOUNT_PRECISION);
    }
}
