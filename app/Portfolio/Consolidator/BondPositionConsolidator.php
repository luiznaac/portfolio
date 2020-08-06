<?php

namespace App\Portfolio\Consolidator;

use App\Model\Bond\Bond;
use App\Model\Bond\BondOrder;
use App\Model\Bond\BondPosition;
use App\Model\Index\Index;
use App\Model\Index\IndexValue;
use App\Model\Log\Log;
use App\Portfolio\Utils\BatchInsertOrUpdate;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;

class BondPositionConsolidator implements ConsolidatorInterface {

    private const ENTITY_NAME = 'BondPositionConsolidator';
    private static $positions_buffer = [];

    public static function consolidate(): void {
        $bond_orders_dates = ConsolidatorDateProvider::getBondPositionDatesToBeUpdated();

        foreach ($bond_orders_dates as $bond_order_id => $date) {
            $bond_order = BondOrder::find($bond_order_id);
            $date = Carbon::parse($date);

            try {
                self::consolidateBondPositions($bond_order, $date);
            } catch(\Throwable $e) {
                Log::log(Log::EXCEPTION_TYPE, self::ENTITY_NAME.'::'.__FUNCTION__, $e->getMessage());
            }
        }

        self::savePositionsBuffer();
        self::touchLastBondPosition();
    }

    private static function consolidateBondPositions(BondOrder $bond_order, Carbon $start_date) {
        $end_date = Calendar::getLastMarketWorkingDate()->subDay();
        $dates = Calendar::getWorkingDaysDatesForRange($start_date, $end_date);
        $bond = Bond::find($bond_order->bond_id);
        $index_values = self::getIndexValues($bond, $dates);

        foreach ($dates as $date) {
            $position = $position ?? self::getBondPositionOrEmpty($bond_order, $start_date);

            if(empty($position)) {
                $position['bond_order_id'] = $bond_order->id;
                $position['amount'] = $bond_order->amount;
                $position['contributed_amount'] = $bond_order->amount;
            }

            $position['date'] = Carbon::parse($date);
            $position = self::calculateAmount(Carbon::parse($date), $position, $bond, $index_values);

            self::$positions_buffer[] = $position;
        }
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

    private static function getBondPositionOrEmpty(BondOrder $bond_order, Carbon $date): array {
        $date = Calendar::getLastWorkingDayForDate((clone $date)->subDay());

        return BondPosition::getBaseQuery()
            ->where('bond_order_id', $bond_order->id)
            ->where('date', $date->toDateString())
            ->get()->toArray()[0] ?? [];
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

    private static function savePositionsBuffer(): void {
        $data = [];
        foreach (self::$positions_buffer as $position) {
            if($position['amount'] == 0) {
                continue;
            }

            $data[] = [
                'user_id'               => auth()->id(),
                'bond_order_id'         => $position['bond_order_id'],
                'date'                  => $position['date']->toDateString(),
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
