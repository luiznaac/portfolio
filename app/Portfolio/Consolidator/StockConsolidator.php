<?php

namespace App\Portfolio\Consolidator;

use App\Model\Log\Log;
use App\Model\Order\Order;
use App\Model\Stock\Dividend\StockDividend;
use App\Model\Stock\Dividend\StockDividendStatementLine;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Model\Stock\StockPrice;
use App\Portfolio\Utils\BatchInsertOrUpdate;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class StockConsolidator {

    /** @var Stock $stock */
    private static $stock;
    private static $prices;
    private static $dates_updated;
    private static $positions_buffer = [];
    private static $dividend_lines_buffer = [];

    public static function consolidate(): void {
        Log::log('debug', __CLASS__.'::'.__FUNCTION__, 'starting at:' . Carbon::now()->toDateTimeString());
        self::clear();
        self::consolidateStockPositions();
        self::consolidateDividends();
        Log::log('debug', __CLASS__.'::'.__FUNCTION__, 'finishing at:' . Carbon::now()->toDateTimeString());
    }

    private static function getStockDatesToBeUpdated(): array {
        return self::$dates_updated ?? self::$dates_updated = self::calculateStockDatesToBeUpdated();
    }

    private static function calculateStockDatesToBeUpdated() {
        $stock_ids_orders_dates = self::getOldestDateOfLastInsertedOrdersForEachStock();
        $stock_ids_positions_dates = self::getLastDateOfOutdatedStockPositionsForEachStock();

        return self::mergeDatesConsideringTheOldestOne($stock_ids_orders_dates, $stock_ids_positions_dates);
    }

    private static function getStockDividendsDatesToBeUpdated(): array {
        $oldest_missing_dividend_dates = StockDividendStatementLine::getOldestDateOfMissingStockDividendStatementLineForEachStock();

        return self::mergeDatesConsideringTheOldestOne($oldest_missing_dividend_dates, self::getStockDatesToBeUpdated());
    }

    public static function shouldConsolidate(): bool {
        return !empty(self::getStockDatesToBeUpdated())
            || !empty(self::getStockPositionIdsToRemove())
            || !empty(self::getStockDividendsDatesToBeUpdated());
    }

    private static function consolidateStockPositions(): void {
        self::removePositionsWithoutOrders();
        $stocks_dates = self::getStockDatesToBeUpdated();

        foreach ($stocks_dates as $stock_id => $date) {
            $stock = Stock::find($stock_id);
            $date = Carbon::parse($date);

            self::consolidateStockPositionsForStock($stock, $date);
        }

        self::savePositionsBuffer();
        self::touchLastStockPosition();
    }

    private static function consolidateDividends(): void {
        self::removeDividendStatementLinesWithoutOrders();
        $stock_dividends_dates = self::getStockDividendsDatesToBeUpdated();

        foreach ($stock_dividends_dates as $stock_id => $date) {
            $stock = Stock::find($stock_id);
            $date = Carbon::parse($date);

            self::consolidateDividendsForStock($stock, $date);
        }

        self::saveDividendLinesBuffer();
    }

    private static function consolidateStockPositionsForStock(Stock $stock, Carbon $start_date) {
        self::$stock = $stock;
        $end_date = Calendar::getLastMarketWorkingDate();
        $dates = Calendar::getWorkingDaysDatesForRange($start_date, $end_date);
        $grouped_orders = self::getOrdersGroupedByDateInRange($start_date, $end_date);
        self::loadAndCachePricesBeforeProcessing($dates);

        foreach ($dates as $date) {
            $position = $position ?? self::getStockPositionOrEmpty($stock, $start_date);
            $position['stock_id'] = $stock->id;
            $position['date'] = Carbon::parse($date);

            if(isset($grouped_orders[$date])) {
                $orders_in_this_date = $grouped_orders[$date];
                $position = self::sumOrdersToPosition($orders_in_this_date, $position);
            }

            $position = self::calculateAmountAccordinglyPriceOnDate(Carbon::parse($date), $position);

            self::$positions_buffer[] = $position;
        }
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

    private static function getOrdersGroupedByDateInRange(Carbon $start_date, Carbon $end_date): array {
        $orders = Order::getAllOrdersForStockInRage(self::$stock, $start_date, $end_date);

        $grouped_orders = [];
        /** @var Order $order */
        foreach ($orders as $order) {
            $grouped_orders[$order->date][] = $order;
        }

        return $grouped_orders;
    }

    private static function loadAndCachePricesBeforeProcessing(array $dates): void {
        if(sizeof($dates) < 1) {
            return;
        }

        $start_date = Carbon::parse($dates[0]);
        $end_date = Carbon::parse($dates[sizeof($dates)-1]);

        $stock_prices = StockPrice::getStockPricesForDateRange(self::$stock, $start_date, $end_date);

        self::$prices = [];
        /** @var StockPrice $stock_price */
        foreach ($stock_prices as $stock_price) {
            self::$prices[$stock_price['date']] = $stock_price['price'];
        }
    }

    private static function getStockPositionOrEmpty(Stock $stock, Carbon $date): array {
        $date = Calendar::getLastWorkingDayForDate((clone $date)->subDay());

        return StockPosition::getBaseQuery()
            ->where('stock_id', $stock->id)
            ->where('date', $date->toDateString())
            ->get()->toArray()[0] ?? [];
    }

    private static function sumOrdersToPosition(array $orders, array $position): array {
        foreach ($orders as $order) {
            $position['quantity'] =
                (isset($position['quantity']) ? $position['quantity'] : 0) + $order['quantity'];
            $position['contributed_amount'] =
                (isset($position['contributed_amount']) ? $position['contributed_amount'] : 0) + $order['quantity'] * $order['price'] + $order['cost'];
        }

        $position['average_price'] = $position['contributed_amount']/$position['quantity'];

        return $position;
    }

    private static function calculateAmountAccordinglyPriceOnDate(Carbon $date, array $position): array {
        $price_on_date = self::getStockPriceFromCacheForDate($date);

        $position['amount'] = $price_on_date * $position['quantity'];

        return $position;
    }

    private static function getStockPriceFromCacheForDate(Carbon $date): float {
        if(!isset(self::$prices[$date->toDateString()])) {
            return 0;
        }

        return self::$prices[$date->toDateString()];
    }

    private static function savePositionsBuffer(): void {
        $data = [];
        foreach (self::$positions_buffer as $position) {
            if($position['amount'] == 0) {
                continue;
            }

            $data[] = [
                'user_id'   => auth()->id(),
                'stock_id'  => $position['stock_id'],
                'date'      => $position['date']->toDateString(),
                'quantity'              => $position['quantity'],
                'amount'                => $position['amount'],
                'contributed_amount'    => $position['contributed_amount'],
                'average_price'         => $position['average_price'],
            ];
        }

        BatchInsertOrUpdate::execute('stock_positions', $data);
        self::$positions_buffer = [];
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

    private static function removePositionsWithoutOrders(): void {
        $stock_position_ids_to_remove = self::getStockPositionIdsToRemove();

        StockPosition::getBaseQuery()->whereIn('id', $stock_position_ids_to_remove)->delete();
    }

    private static function removeDividendStatementLinesWithoutOrders(): void {
        $dividend_statement_line_ids_to_remove = self::getDividendStatementLineIdsToRemove();

        StockDividendStatementLine::getBaseQuery()->whereIn('id', $dividend_statement_line_ids_to_remove)->delete();
    }

    private static function getStockPositionIdsToRemove(): array {
        $query = <<<SQL
SELECT sp.id AS stock_position_id
FROM stock_positions sp
         LEFT JOIN orders o ON sp.stock_id = o.stock_id AND sp.user_id = o.user_id
WHERE sp.user_id = ?
  AND (o.id IS NULL
    OR sp.date < (SELECT MIN(date) FROM orders o2 WHERE sp.stock_id = o2.stock_id AND sp.user_id = o2.user_id));
SQL;

        $rows = DB::select($query, [auth()->id()]);

        $data = [];
        foreach ($rows as $row) {
            $data[] = $row->stock_position_id;
        }

        return $data;
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

    private static function touchLastStockPosition(): void {
        /** @var StockPosition $last_stock_position */
        $last_stock_position = StockPosition::getBaseQuery()->orderByDesc('id')->get()->first();
        if($last_stock_position) {
            $last_stock_position->touch();
        }
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

    private static function mergeDatesConsideringTheOldestOne(array $stock_ids_dates_1, array $stock_ids_dates_2): array {
        $ids = array_merge(array_keys($stock_ids_dates_1), array_keys($stock_ids_dates_2));

        $data = [];
        foreach ($ids as $id) {
            if (isset($stock_ids_dates_1[$id]) && isset($stock_ids_dates_2[$id])) {
                $date_1 = Carbon::parse($stock_ids_dates_1[$id]);
                $date_2 = Carbon::parse($stock_ids_dates_2[$id]);
                $data[$id] = $date_1->lte($date_2) ? $date_1->toDateString() : $date_2->toDateString();

                continue;
            }

            $data[$id] = $stock_ids_dates_1[$id] ?? $stock_ids_dates_2[$id];
        }

        return $data;
    }

    private static function clear(): void {
        self::$positions_buffer = [];
        self::$dividend_lines_buffer = [];
        self::$dates_updated = null;
    }
}
