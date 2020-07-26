<?php

namespace App\Portfolio\Consolidator;

use App\Model\Stock\Dividend\StockDividend;
use App\Model\Stock\Dividend\StockDividendStatementLine;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Portfolio\Utils\BatchInsertOrUpdate;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockDividendConsolidator implements ConsolidatorInterface {

    private static $dividend_lines_buffer = [];

    public static function consolidate(): void {
        self::removeDividendStatementLinesWithoutOrders();
        $stock_dividends_dates = ConsolidatorDateProvider::getStockDividendDatesToBeUpdated();

        foreach ($stock_dividends_dates as $stock_id => $date) {
            $stock = Stock::find($stock_id);
            $date = Carbon::parse($date);

            self::consolidateDividendsForStock($stock, $date);
        }

        self::saveDividendLinesBuffer();
    }

    private static function consolidateDividendsForStock(Stock $stock, Carbon $start_date) {
        $stock_dividends = self::getStockDividends($stock, $start_date);
        if (empty($stock_dividends)) {
            return;
        }

        $stock_positions = self::getStockPositionsForDates($stock, array_keys($stock_dividends));

        foreach ($stock_dividends as $date => $dividend_info) {
            $quantity = $stock_positions[$date]['quantity'];

            $dividend_line = [
                'stock_dividend_id' => $dividend_info['id'],
                'quantity' => $quantity,
                'amount_paid' => $quantity * $dividend_info['value'],
            ];

            self::$dividend_lines_buffer[] = $dividend_line;
        }
    }

    private static function getStockDividends(Stock $stock, Carbon $start_date) {
        $stock_dividends = StockDividend::getStockDividendsStoredInReferenceDateRange($stock, $start_date, Calendar::getLastMarketWorkingDate());

        $data = [];
        /** @var StockDividend $stock_dividend */
        foreach ($stock_dividends as $stock_dividend) {
            $data[$stock_dividend['reference_date']] = [
                'id' => $stock_dividend['id'],
                'value' => $stock_dividend['value'],
            ];
        }

        return $data;
    }

    private static function getStockPositionsForDates(Stock $stock, array $dates): array {
        $start_date = Carbon::parse($dates[0]);
        $end_date = Carbon::parse($dates[sizeof($dates)-1]);
        $stock_positions = StockPosition::getPositionsForStockInRange($stock, $start_date, $end_date);

        $data = [];
        /** @var StockPosition $stock_position */
        foreach ($stock_positions as $stock_position) {
            if (!in_array($stock_position->date, $dates)) {
                continue;
            }

            $data[$stock_position->date] = [
                'quantity' => $stock_position->quantity,
            ];
        }

        return $data;
    }

    private static function saveDividendLinesBuffer(): void {
        $data = [];
        foreach (self::$dividend_lines_buffer as $line) {
            $line['user_id'] = auth()->id();

            $data[] = $line;
        }

        BatchInsertOrUpdate::execute('stock_dividend_statement_lines', $data);
        self::$dividend_lines_buffer = [];
    }

    private static function removeDividendStatementLinesWithoutOrders(): void {
        $dividend_statement_line_ids_to_remove = self::getDividendStatementLineIdsToRemove();

        StockDividendStatementLine::getBaseQuery()->whereIn('id', $dividend_statement_line_ids_to_remove)->delete();
    }

    private static function getDividendStatementLineIdsToRemove(): array {
        $query = <<<SQL
SELECT dl.id AS stock_dividend_statement_line_id
FROM stock_dividend_statement_lines dl
    LEFT JOIN stock_dividends sd ON dl.stock_dividend_id = sd.id
    LEFT JOIN orders o ON sd.stock_id = o.stock_id AND dl.user_id = o.user_id
WHERE dl.user_id = ?
  AND (o.id IS NULL
    OR sd.reference_date < (SELECT MIN(date) FROM orders o2 WHERE sd.stock_id = o2.stock_id AND dl.user_id = o2.user_id));
SQL;

        $rows = DB::select($query, [auth()->id()]);

        $data = [];
        foreach ($rows as $row) {
            $data[] = $row->stock_dividend_statement_line_id;
        }

        return $data;
    }
}
