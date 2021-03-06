<?php

namespace App\Portfolio\Consolidator;

use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConsolidatorDateProvider {

    private static $stock_dates;
    private static $bond_dates;
    private static $treasury_bond_dates;

    public static function getOldestLastReferenceDate(): ?Carbon {
        $date = self::calculateOldestLastReferenceDate();

        return $date ? Carbon::parse($date) : null;
    }

    public static function getStockPositionDatesToBeUpdated(): array {
        return self::$stock_dates ?? self::$stock_dates = self::calculateStockPositionDatesToBeUpdated();
    }

    public static function getStockDividendDatesToBeUpdated(): array {
        return self::calculateStockDividendDatesToBeUpdated();
    }

    public static function getBondPositionDatesToBeUpdated(): array {
        return self::$bond_dates ?? self::$bond_dates = self::calculateBondPositionDatesToBeUpdated();
    }

    public static function getTreasuryBondPositionDatesToBeUpdated(): array {
        return self::$treasury_bond_dates ?? self::$treasury_bond_dates = self::calculateTreasuryBondPositionDatesToBeUpdated();
    }

    public static function clearCache(): void {
        self::$stock_dates = null;
        self::$bond_dates = null;
        self::$treasury_bond_dates = null;
    }

    private static function calculateOldestLastReferenceDate(): ?string {
        $query = <<<SQL
SELECT MIN(last_date) AS oldest_last_date
FROM (SELECT MAX(date) AS last_date
      FROM stock_positions sp
      WHERE user_id = ?
        AND (SELECT SUM(IF(o.type = 'sell', -o.quantity, o.quantity))
             FROM orders o
             WHERE sp.user_id = o.user_id
               AND sp.stock_id = o.stock_id) > 0
      GROUP BY stock_id) last_positions;
SQL;

        $record = DB::select($query, [auth()->id()])[0];

        return $record->oldest_last_date;
    }

    private static function calculateStockPositionDatesToBeUpdated(): array {
        $stock_ids_orders_dates = self::getOldestDateOfLastInsertedOrdersForEachStock();
        $stock_ids_positions_dates = self::getLastDateOfOutdatedStockPositionsForEachStock();

        return self::mergeDatesConsideringTheOldestOne($stock_ids_orders_dates, $stock_ids_positions_dates);
    }

    private static function calculateStockDividendDatesToBeUpdated(): array {
        $oldest_missing_dividend_dates = self::getOldestDateOfMissingStockDividendStatementLineForEachStock();

        return self::mergeDatesConsideringTheOldestOne($oldest_missing_dividend_dates, self::getStockPositionDatesToBeUpdated());
    }

    private static function getOldestDateOfLastInsertedOrdersForEachStock(): array {
        $query = <<<SQL
SELECT o.stock_id, MIN(o.date) AS order_date
FROM orders o
         LEFT JOIN stock_positions sp ON o.stock_id = sp.stock_id AND o.user_id = sp.user_id
WHERE o.user_id = ?
  AND (o.updated_at >
       (SELECT MAX(updated_at) FROM stock_positions sp2 WHERE sp2.stock_id = sp.stock_id AND sp2.user_id = sp.user_id)
    OR sp.id IS NULL)
GROUP BY o.stock_id;
SQL;

        $rows = DB::select($query, [auth()->id()]);

        $data = [];
        foreach ($rows as $row) {
            $data[$row->stock_id] = $row->order_date;
        }

        return $data;
    }

    private static function getLastDateOfOutdatedStockPositionsForEachStock(): array {
        $query = <<<SQL
SELECT stock_id, last_date
FROM (SELECT MAX(date) AS last_date, stock_id
      FROM stock_positions sp
      WHERE user_id = ?
      GROUP BY stock_id) last_positions
WHERE last_date < ?;
SQL;

        $rows = DB::select($query, [
                auth()->id(),
                Calendar::getLastMarketWorkingDate()->toDateString()
            ]
        );

        $data = [];
        foreach ($rows as $row) {
            $data[$row->stock_id] = $row->last_date;
        }

        return $data;
    }

    private static function getOldestDateOfMissingStockDividendStatementLineForEachStock(): array {
        $query = <<<SQL
SELECT MIN(reference_date) AS oldest_missing_date, sd.stock_id
FROM orders o
    JOIN stock_dividends sd ON o.stock_id = sd.stock_id AND o.date <= reference_date
    LEFT JOIN stock_dividend_statement_lines dl ON sd.id = dl.stock_dividend_id AND o.user_id = dl.user_id
WHERE o.user_id = ?
  AND dl.id IS NULL
  AND sd.date_paid <= ?
  AND (SELECT SUM(IF(o2.type = 'sell', -o2.quantity, o2.quantity))
       FROM orders o2
       WHERE o.user_id = o2.user_id
         AND o.stock_id = o2.stock_id
         AND o2.date <= reference_date) > 0
GROUP BY sd.stock_id;
SQL;

        $rows = DB::select($query, [auth()->id(), Calendar::getLastMarketWorkingDate()]);

        $data = [];
        foreach ($rows as $row) {
            $data[$row->stock_id] = $row->oldest_missing_date;
        }

        return $data;
    }

    private static function calculateBondPositionDatesToBeUpdated(): array {
        return self::getDatesForEachBondOrder();
    }

    private static function getDatesForEachBondOrder(): array {
        $query = <<<SQL
SELECT bo.id, IF(bp.id IS NULL, bo.date, bp.date) AS bond_order_date
FROM bond_orders bo
         LEFT JOIN bond_positions bp ON bo.id = bp.bond_order_id AND bo.user_id = bp.user_id
WHERE bo.user_id = ?
  AND (bp.id IS NULL
    OR (bp.date = (SELECT MAX(date) FROM bond_positions bp2 WHERE bo.id = bp2.bond_order_id)
        AND bp.date < ?));
SQL;

        $rows = DB::select($query, [
                auth()->id(),
                Calendar::getLastMarketWorkingDate()->toDateString()
            ]
        );

        $data = [];
        foreach ($rows as $row) {
            $data[$row->id] = $row->bond_order_date;
        }

        return $data;
    }

    private static function calculateTreasuryBondPositionDatesToBeUpdated(): array {
        $treasury_bond_ids_orders_dates = self::getOldestDateOfLastInsertedOrdersForEachTreasuryBond();
        $treasury_bond_ids_positions_dates = self::getLastDateOfOutdatedBondPositionsForEachTreasuryBond();

        return self::mergeDatesConsideringTheOldestOne($treasury_bond_ids_orders_dates, $treasury_bond_ids_positions_dates);
    }

    private static function getOldestDateOfLastInsertedOrdersForEachTreasuryBond(): array {
        $query = <<<SQL
SELECT o.treasury_bond_id, MIN(o.date) AS order_date
FROM treasury_bond_orders o
         LEFT JOIN treasury_bond_positions tbp ON o.treasury_bond_id = tbp.treasury_bond_id AND o.user_id = tbp.user_id
WHERE o.user_id = ?
  AND (o.updated_at >
       (SELECT MAX(updated_at) FROM treasury_bond_positions tbp2 WHERE tbp2.treasury_bond_id = tbp.treasury_bond_id AND tbp2.user_id = tbp.user_id)
    OR tbp.id IS NULL)
GROUP BY o.treasury_bond_id;
SQL;

        $rows = DB::select($query, [auth()->id()]);

        $data = [];
        foreach ($rows as $row) {
            $data[$row->treasury_bond_id] = $row->order_date;
        }

        return $data;
    }

    private static function getLastDateOfOutdatedBondPositionsForEachTreasuryBond(): array {
        $query = <<<SQL
SELECT treasury_bond_id, last_date
FROM (SELECT MAX(date) AS last_date, treasury_bond_id
      FROM treasury_bond_positions rbp
      WHERE user_id = ?
      GROUP BY treasury_bond_id) last_positions
WHERE last_date < ?;
SQL;

        $rows = DB::select($query, [
                auth()->id(),
                Calendar::getLastMarketWorkingDate()->toDateString()
            ]
        );

        $data = [];
        foreach ($rows as $row) {
            $data[$row->treasury_bond_id] = $row->last_date;
        }

        return $data;
    }

    private static function mergeDatesConsideringTheOldestOne(array $ids_dates_1, array $ids_dates_2): array {
        $ids = array_merge(array_keys($ids_dates_1), array_keys($ids_dates_2));

        $data = [];
        foreach ($ids as $id) {
            if (isset($ids_dates_1[$id]) && isset($ids_dates_2[$id])) {
                $date_1 = Carbon::parse($ids_dates_1[$id]);
                $date_2 = Carbon::parse($ids_dates_2[$id]);
                $data[$id] = $date_1->lte($date_2) ? $date_1->toDateString() : $date_2->toDateString();

                continue;
            }

            $data[$id] = $ids_dates_1[$id] ?? $ids_dates_2[$id];
        }

        return $data;
    }
}
