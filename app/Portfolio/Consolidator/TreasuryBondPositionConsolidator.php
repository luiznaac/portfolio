<?php

namespace App\Portfolio\Consolidator;

use App\Model\Bond\Treasury\TreasuryBond;
use App\Model\Bond\Treasury\TreasuryBondOrder;
use App\Model\Bond\Treasury\TreasuryBondPosition;
use App\Model\Index\Index;
use App\Model\Index\IndexValue;
use App\Model\Log\Log;
use App\Portfolio\Utils\BatchInsertOrUpdate;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TreasuryBondPositionConsolidator implements ConsolidatorInterface {

    private const ENTITY_NAME = 'TreasuryBondPositionConsolidator';
    private static $positions_buffer = [];

    public static function consolidate(): void {
        self::removePositionsWithoutOrders();
        $treasury_bonds_dates = ConsolidatorDateProvider::getTreasuryBondPositionDatesToBeUpdated();

        foreach ($treasury_bonds_dates as $treasury_bond_id => $date) {
            $treasury_bond = TreasuryBond::find($treasury_bond_id);
            $date = Carbon::parse($date);

            try {
                self::consolidateTreasuryBondPositions($treasury_bond, $date);
            } catch(\Throwable $e) {
                Log::log(Log::EXCEPTION_TYPE, self::ENTITY_NAME.'::'.__FUNCTION__, $e->getMessage());
            }
        }

        self::savePositionsBuffer();
        self::touchLastBondPosition();
    }

    private static function consolidateTreasuryBondPositions(TreasuryBond $treasury_bond, Carbon $start_date) {
        $end_date = Calendar::getLastMarketWorkingDate()->subDay();
        $dates = Calendar::getWorkingDaysDatesForRange($start_date, $end_date);
        $grouped_orders = self::getOrdersGroupedByDateInRange($treasury_bond, $start_date, $end_date);
        $index_values = self::getIndexValues($treasury_bond, $dates);

        foreach ($dates as $date) {
            $position = $position ?? self::getTreasuryBondPositionOrEmpty($treasury_bond, $start_date);
            $position['treasury_bond_id'] = $treasury_bond->id;
            $position['date'] = Carbon::parse($date);

            if(isset($grouped_orders[$date])) {
                $orders_in_this_date = $grouped_orders[$date];
                $position = self::sumOrdersToPosition($orders_in_this_date, $position);
            }

            $position = self::calculateAmount(Carbon::parse($date), $position, $treasury_bond, $index_values);

            self::$positions_buffer[] = $position;
        }
    }

    private static function getOrdersGroupedByDateInRange(TreasuryBond $treasury_bond, Carbon $start_date, Carbon $end_date): array {
        $orders = TreasuryBondOrder::getAllOrdersForTreasuryBondInRange($treasury_bond, $start_date, $end_date);

        $grouped_orders = [];
        /** @var TreasuryBondOrder $order */
        foreach ($orders as $order) {
            $grouped_orders[$order->date][] = $order;
        }

        return $grouped_orders;
    }

    private static function getIndexValues(TreasuryBond $treasury_bond, array $dates): array {
        if(sizeof($dates) < 1 || !$treasury_bond->index_id) {
            return [];
        }

        $start_date = Carbon::parse($dates[0]);
        $end_date = Carbon::parse($dates[sizeof($dates)-1]);

        $index_values = IndexValue::getValuesForDateRange(Index::find($treasury_bond->index_id), $start_date, $end_date);

        $values = [];
        /** @var IndexValue $index_value */
        foreach ($index_values as $index_value) {
            $values[$index_value['date']] = $index_value['value'];
        }

        return $values;
    }

    private static function getTreasuryBondPositionOrEmpty(TreasuryBond $treasury_bond, Carbon $date): array {
        $date = Calendar::getLastWorkingDayForDate((clone $date)->subDay());

        return TreasuryBondPosition::getBaseQuery()
            ->where('treasury_bond_id', $treasury_bond->id)
            ->where('date', $date->toDateString())
            ->get()->toArray()[0] ?? [];
    }

    private static function sumOrdersToPosition(array $orders, array $position): array {
        foreach ($orders as $order) {
            $position = self::handleCalculationAccordinglyOrderType($order, $position);
        }

        return $position;
    }

    private static function handleCalculationAccordinglyOrderType(TreasuryBondOrder $order, array $position): array {
        $position['contributed_amount'] =
            (isset($position['contributed_amount']) ? $position['contributed_amount'] : 0) + $order['amount'];

        $position['amount'] =
            (isset($position['amount']) ? $position['amount'] : 0) + $order['amount'];

        return $position;
    }

    private static function calculateAmount(Carbon $date, array $position, TreasuryBond $treasury_bond, array $index_values): array {
        $daily_return = self::calculateTotalDailyReturn($date, $treasury_bond, $index_values);
        $position['amount'] = $position['amount'] * (1 + $daily_return);

        return $position;
    }

    private static function calculateTotalDailyReturn(Carbon $date, TreasuryBond $treasury_bond, array $index_values): float {
        $return = 0;

        if($treasury_bond->index_id && $treasury_bond->index_rate) {
            $return += ($treasury_bond->index_rate/100) * ($index_values[$date->toDateString()]/100);
        }

        if($treasury_bond->interest_rate) {
            $return += pow((1 + $treasury_bond->interest_rate/100), 1/252) - 1;
        }

        return $return;
    }

    private static function removePositionsWithoutOrders(): void {
        $treasury_bond_position_ids_to_remove = self::getBondPositionIdsToRemove();

        TreasuryBondPosition::getBaseQuery()->whereIn('id', $treasury_bond_position_ids_to_remove)->delete();
    }

    private static function getBondPositionIdsToRemove(): array {
        $query = <<<SQL
SELECT tbp.id AS treasury_bond_position_id
FROM treasury_bond_positions tbp
         LEFT JOIN treasury_bond_orders o ON tbp.treasury_bond_id = o.treasury_bond_id AND tbp.user_id = o.user_id
WHERE tbp.user_id = ?
  AND (o.id IS NULL
    OR tbp.date <= (SELECT MIN(date) FROM treasury_bond_orders o2 WHERE tbp.treasury_bond_id = o2.treasury_bond_id AND tbp.user_id = o2.user_id));
SQL;

        $rows = DB::select($query, [auth()->id()]);

        $data = [];
        foreach ($rows as $row) {
            $data[] = $row->treasury_bond_position_id;
        }

        return $data;
    }

    private static function savePositionsBuffer(): void {
        $data = [];
        foreach (self::$positions_buffer as $position) {
            if($position['amount'] == 0) {
                continue;
            }

            $data[] = [
                'user_id'   => auth()->id(),
                'treasury_bond_id'  => $position['treasury_bond_id'],
                'date'      => $position['date']->toDateString(),
                'amount'                => $position['amount'],
                'contributed_amount'    => $position['contributed_amount'],
            ];
        }

        BatchInsertOrUpdate::execute('treasury_bond_positions', $data);
        self::$positions_buffer = [];
    }

    private static function touchLastBondPosition(): void {
        /** @var TreasuryBondPosition $last_treasury_bond_position */
        $last_treasury_bond_position = TreasuryBondPosition::getBaseQuery()->orderByDesc('id')->get()->first();
        if($last_treasury_bond_position) {
            $last_treasury_bond_position->touch();
        }
    }
}
