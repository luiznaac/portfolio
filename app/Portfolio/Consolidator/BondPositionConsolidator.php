<?php

namespace App\Portfolio\Consolidator;

use App\Model\Bond\Bond;
use App\Model\Bond\BondOrder;
use App\Model\Bond\BondPosition;
use App\Model\Index\Index;
use App\Model\Index\IndexValue;
use App\Portfolio\Utils\BatchInsertOrUpdate;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BondPositionConsolidator implements ConsolidatorInterface {

    private static $positions_buffer = [];

    public static function consolidate(): void {
        self::removePositionsWithoutOrders();
        $bonds_dates = ConsolidatorDateProvider::getBondPositionDatesToBeUpdated();

        foreach ($bonds_dates as $bond_id => $date) {
            $bond = Bond::find($bond_id);
            $date = Carbon::parse($date);

            self::consolidateBondPositions($bond, $date);
        }

        self::savePositionsBuffer();
        self::touchLastBondPosition();
    }

    private static function consolidateBondPositions(Bond $bond, Carbon $start_date) {
        $end_date = Calendar::getLastMarketWorkingDate();
        $dates = Calendar::getWorkingDaysDatesForRange($start_date, $end_date);
        $grouped_orders = self::getOrdersGroupedByDateInRange($bond, $start_date, $end_date);
        $index_values = self::getIndexValues($bond, $dates);

        foreach ($dates as $date) {
            $position = $position ?? self::getBondPositionOrEmpty($bond, $start_date);
            $position['bond_id'] = $bond->id;
            $position['date'] = Carbon::parse($date);

            if(isset($grouped_orders[$date])) {
                $orders_in_this_date = $grouped_orders[$date];
                $position = self::sumOrdersToPosition($orders_in_this_date, $position);
            }

            $position = self::calculateAmount(Carbon::parse($date), $position, $bond, $index_values);

            self::$positions_buffer[] = $position;
        }
    }

    private static function getOrdersGroupedByDateInRange(Bond $bond, Carbon $start_date, Carbon $end_date): array {
        $orders = BondOrder::getAllOrdersForBondInRange($bond, $start_date, $end_date);

        $grouped_orders = [];
        /** @var BondOrder $order */
        foreach ($orders as $order) {
            $grouped_orders[$order->date][] = $order;
        }

        return $grouped_orders;
    }

    private static function getIndexValues(Bond $bond, array $dates): array {
        if(sizeof($dates) < 1 || !$bond->index_id) {
            return [];
        }

        $start_date = Carbon::parse($dates[0]);
        $end_date = Carbon::parse($dates[sizeof($dates)-1]);

        $index_values = IndexValue::getValuesForDateRange(Index::find($bond->index_id), $start_date, $end_date);

        $values = [];
        /** @var IndexValue $index_value */
        foreach ($index_values as $index_value) {
            $values[$index_value['date']] = $index_value['value'];
        }

        return $values;
    }

    private static function getBondPositionOrEmpty(Bond $bond, Carbon $date): array {
        $date = Calendar::getLastWorkingDayForDate((clone $date)->subDay());

        return BondPosition::getBaseQuery()
            ->where('bond_id', $bond->id)
            ->where('date', $date->toDateString())
            ->get()->toArray()[0] ?? [];
    }

    private static function sumOrdersToPosition(array $orders, array $position): array {
        foreach ($orders as $order) {
            $position = self::handleCalculationAccordinglyOrderType($order, $position);
        }

        return $position;
    }

    private static function handleCalculationAccordinglyOrderType(BondOrder $order, array $position): array {
        $position['contributed_amount'] =
            (isset($position['contributed_amount']) ? $position['contributed_amount'] : 0) + $order['amount'];

        $position['amount'] =
            (isset($position['amount']) ? $position['amount'] : 0) + $order['amount'];

        return $position;
    }

    private static function calculateAmount(Carbon $date, array $position, Bond $bond, array $index_values): array {
        $daily_return = self::calculateTotalDailyReturn($date, $bond, $index_values);
        $position['amount'] = $position['amount'] * (1 + $daily_return);

        return $position;
    }

    private static function calculateTotalDailyReturn(Carbon $date, Bond $bond, array $index_values): float {
        $return = 0;

        if($bond->index_id && $bond->index_rate) {
            $return += ($bond->index_rate/100) * ($index_values[$date->toDateString()]/100);
        }

        if($bond->interest_rate) {
            $return += pow((1 + $bond->interest_rate/100), 1/252) - 1;
        }

        return $return;
    }

    private static function removePositionsWithoutOrders(): void {
        $bond_position_ids_to_remove = self::getBondPositionIdsToRemove();

        BondPosition::getBaseQuery()->whereIn('id', $bond_position_ids_to_remove)->delete();
    }

    private static function getBondPositionIdsToRemove(): array {
        $query = <<<SQL
SELECT bp.id AS bond_position_id
FROM bond_positions bp
         LEFT JOIN bond_orders o ON bp.bond_id = o.bond_id AND bp.user_id = o.user_id
WHERE bp.user_id = ?
  AND (o.id IS NULL
    OR bp.date <= (SELECT MIN(date) FROM bond_orders o2 WHERE bp.bond_id = o2.bond_id AND bp.user_id = o2.user_id));
SQL;

        $rows = DB::select($query, [auth()->id()]);

        $data = [];
        foreach ($rows as $row) {
            $data[] = $row->bond_position_id;
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
                'bond_id'  => $position['bond_id'],
                'date'      => $position['date']->toDateString(),
                'amount'                => $position['amount'],
                'contributed_amount'    => $position['contributed_amount'],
            ];
        }

        BatchInsertOrUpdate::execute('bond_positions', $data);
        self::$positions_buffer = [];
    }

    private static function touchLastBondPosition(): void {
        /** @var BondPosition $last_bond_position */
        $last_bond_position = BondPosition::getBaseQuery()->orderByDesc('id')->get()->first();
        if($last_bond_position) {
            $last_bond_position->touch();
        }
    }
}
