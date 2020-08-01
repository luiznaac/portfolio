<?php

namespace App\Portfolio\Consolidator;

use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConsolidatorDateProvider {

    private static $stock_dates;
    private static $bond_dates;

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

    public static function clearCache(): void {
        self::$stock_dates = null;
        self::$bond_dates = null;
    }

    private static function calculateOldestLastReferenceDate(): ?string {
        $query = <<<SQL
SELECT MIN(last_date) AS oldest_last_date
FROM (SELECT MAX(date) AS last_date
      FROM stock_positions sp
      WHERE user_id = ?
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
        $bond_ids_orders_dates = self::getOldestDateOfLastInsertedOrdersForEachBond();
        $bond_ids_positions_dates = self::getLastDateOfOutdatedBondPositionsForEachBond();

        return self::mergeDatesConsideringTheOldestOne($bond_ids_orders_dates, $bond_ids_positions_dates);
    }

    private static function getOldestDateOfLastInsertedOrdersForEachBond(): array {
        $query = <<<SQL
SELECT o.bond_id, MIN(o.date) AS order_date
FROM bond_orders o
         LEFT JOIN bond_positions bp ON o.bond_id = bp.bond_id AND o.user_id = bp.user_id
WHERE o.user_id = ?
  AND (o.updated_at >
       (SELECT MAX(updated_at) FROM bond_positions bp2 WHERE bp2.bond_id = bp.bond_id AND bp2.user_id = bp.user_id)
    OR bp.id IS NULL)
GROUP BY o.bond_id;
SQL;

        $rows = DB::select($query, [auth()->id()]);

        $data = [];
        foreach ($rows as $row) {
            $data[$row->bond_id] = $row->order_date;
        }

        return $data;
    }

    private static function getLastDateOfOutdatedBondPositionsForEachBond(): array {
        $query = <<<SQL
SELECT bond_id, last_date
FROM (SELECT MAX(date) AS last_date, bond_id
      FROM bond_positions bp
      WHERE user_id = ?
      GROUP BY bond_id) last_positions
WHERE last_date < ?;
SQL;

        $rows = DB::select($query, [
                auth()->id(),
                Calendar::getLastMarketWorkingDate()->toDateString()
            ]
        );

        $data = [];
        foreach ($rows as $row) {
            $data[$row->bond_id] = $row->last_date;
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
