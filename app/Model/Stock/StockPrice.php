<?php

namespace App\Model\Stock;

use App\Model\Log\Log;
use App\Portfolio\Providers\StockPriceProvider;
use App\Portfolio\Utils\BatchInsertOrUpdate;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Stock\StockPrice
 *
 * @property int $id
 * @property int $stock_id
 * @property string $date
 * @property float $price
 */

class StockPrice extends Model {

    protected $fillable = [
        'stock_id',
        'date',
        'price',
    ];

    public static function getStockPricesForDateRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        $stock_prices_stored_in_range = self::getStockPricesStoredInRange($stock, $start_date, $end_date);

        if(!self::hasMissingData($stock_prices_stored_in_range, $start_date, $end_date)) {
            return $stock_prices_stored_in_range;
        }

        self::loadPricesForMissingDates($stock, $stock_prices_stored_in_range, $start_date, $end_date);

        return self::getStockPricesStoredInRange($stock, $start_date, $end_date);
    }

    private static function getStockPricesStoredInRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        return self::query()
            ->where('stock_id', $stock->id)
            ->whereBetween('date', [$start_date, $end_date])
            ->get()->toArray();
    }

    private static function hasMissingData(array $stock_prices_stored_in_range, Carbon $start_date, Carbon $end_date): bool {
        $missing_dates = self::getMissingDates($stock_prices_stored_in_range, $start_date, $end_date);

        return !empty($missing_dates);
    }

    private static function loadPricesForMissingDates(Stock $stock, array $stock_prices_stored_in_range, Carbon $start_date, Carbon $end_date): void {
        $missing_dates = self::getMissingDates($stock_prices_stored_in_range, $start_date, $end_date);
        Log::log('debug', __CLASS__.'::'.__FUNCTION__,$stock->symbol . ' has missing prices ' . implode(', ', $missing_dates));
        $start_date = Carbon::parse($missing_dates[0]);
        $end_date = Carbon::parse($missing_dates[sizeof($missing_dates)-1]);

        self::loadPricesForDatesAndStore($stock, $start_date, $end_date);
    }

    private static function getMissingDates(array $stock_prices_stored_in_range, Carbon $start_date, Carbon $end_date): array {
        $expected_dates = Calendar::getWorkingDaysDatesForRange($start_date, $end_date);
        $dates_stored = self::extractDatesStoredInRange($stock_prices_stored_in_range);

        return array_values(array_diff($expected_dates, $dates_stored));
    }

    private static function loadPricesForDatesAndStore(Stock $stock, Carbon $start_date, Carbon $end_date): void {
        $prices = StockPriceProvider::getPricesForRange($stock, $start_date, $end_date);

        $data = [];
        foreach ($prices as $date => $price) {
            $data[] = [
                'stock_id'  => $stock->id,
                'date'      => $date,
                'price' => $price,
            ];
        }

        BatchInsertOrUpdate::execute('stock_prices', $data);
    }

    private static function extractDatesStoredInRange(array $stock_prices_stored_in_range): array {
        $dates = [];
        foreach ($stock_prices_stored_in_range as $stock_price) {
            $dates[] = $stock_price['date'];
        }

        usort($dates, function ($date_1, $date_2) {
            $date_1 = Carbon::parse($date_1);
            $date_2 = Carbon::parse($date_2);

            return $date_1->lt($date_2);
        });

        return $dates;
    }
}
