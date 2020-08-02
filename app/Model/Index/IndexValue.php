<?php

namespace App\Model\Index;

use App\Model\Log\Log;
use App\Portfolio\Providers\IndexProvider;
use App\Portfolio\Utils\BatchInsertOrUpdate;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Index\IndexValue
 *
 * @property int $id
 * @property int $index_id
 * @property string $date
 * @property float $value
 */

class IndexValue extends Model {

    protected $fillable = ['index_id', 'date', 'value'];

    private static $missing_dates;

    public static function getValuesForDateRange(Index $index, Carbon $start_date, Carbon $end_date): array {
        self::clearCache();
        $values_stored_in_range = self::getValuesStoredInRange($index, $start_date, $end_date);

        if(!self::hasMissingData($index, $values_stored_in_range, $start_date, $end_date)) {
            return $values_stored_in_range;
        }

        self::loadValuesForMissingDates($index, $values_stored_in_range, $start_date, $end_date);

        return self::getValuesStoredInRange($index, $start_date, $end_date);
    }

    private static function getValuesStoredInRange(Index $index, Carbon $start_date, Carbon $end_date): array {
        return self::query()
            ->where('index_id', $index->id)
            ->whereBetween('date', [$start_date, $end_date])
            ->get()->toArray();
    }

    private static function hasMissingData(Index $index, array $values_stored_in_range, Carbon $start_date, Carbon $end_date): bool {
        $missing_dates = self::getMissingDates($index, $values_stored_in_range, $start_date, $end_date);

        return !empty($missing_dates);
    }

    private static function loadValuesForMissingDates(Index $index, array $values_stored_in_range, Carbon $start_date, Carbon $end_date): void {
        $missing_dates = self::getMissingDates($index, $values_stored_in_range, $start_date, $end_date);
        Log::log('debug', __CLASS__.'::'.__FUNCTION__, Index::getIndexAbbr($index->id) . ' has missing values ' . implode(', ', $missing_dates));
        $start_date = Carbon::parse($missing_dates[0]);
        $end_date = Carbon::parse($missing_dates[sizeof($missing_dates)-1]);

        $values = self::loadValuesForDatesAccordinglyIndex($index, $start_date, $end_date);
        self::storeValues($index, $values);
    }

    private static function getMissingDates(Index $index, array $values_stored_in_range, Carbon $start_date, Carbon $end_date): array {
        return self::$missing_dates ?? self::$missing_dates = self::calculateMissingDates($index, $values_stored_in_range, $start_date, $end_date);
    }

    private static function calculateMissingDates(Index $index, array $values_stored_in_range, Carbon $start_date, Carbon $end_date): array {
        $expected_dates = self::getExpectedDatesAccordinglyIndex($index, $start_date, $end_date);
        $dates_stored = self::extractDatesStoredInRange($values_stored_in_range);

        return array_values(array_diff($expected_dates, $dates_stored));
    }

    private static function loadValuesForDatesAccordinglyIndex(Index $index, Carbon $start_date, Carbon $end_date): array {
        if($index->id == Index::IPCA_ID) {
            return self::loadValuesForIPCA($index, (clone $start_date)->startOfMonth(), (clone $end_date)->startOfMonth());
        }

        return IndexProvider::getIndexValuesForRange($index, $start_date, $end_date);
    }

    private static function loadValuesForIPCA(Index $index, Carbon $start_date, Carbon $end_date): array {
        $first_date = (clone $start_date)->subMonths(12)->startOfMonth();
        $values = IndexProvider::getIndexValuesForRange($index, $first_date, $end_date);

        $daily_values = [];
        while($start_date->lte($end_date)) {
            $daily_values = array_merge($daily_values, self::calculateDailyValuesForIPCA($values, clone $start_date));
            $start_date->addMonth();
        }

        return $daily_values;
    }

    private static function calculateDailyValuesForIPCA(array $values, Carbon $date): array {
        $yearly_ipca = self::calculateYearlyIPCA($values, $date);
        $daily_ipca = (pow(1 + $yearly_ipca, 1/252) - 1) * 100;
        $days_of_month = Calendar::getWorkingDaysDatesForRange($date, (clone $date)->endOfMonth());

        $values = [];
        foreach ($days_of_month as $day_of_month) {
            $values[$day_of_month] = $daily_ipca;
        }

        return $values;
    }

    private static function calculateYearlyIPCA(array $values, Carbon $date): float {
        $actual_month = (clone $date)->subMonths(12);

        $yearly_ipca = 1;
        while($actual_month->lt($date)) {
            $month_ipca = $values[$actual_month->toDateString()];
            $yearly_ipca *= 1 + $month_ipca/100;

            $actual_month->addMonth();
        }

        return $yearly_ipca - 1;
    }

    private static function storeValues(Index $index, array $values): void {
        $data = [];
        foreach ($values as $date => $value) {
            $data[] = [
                'index_id'  => $index->id,
                'date'      => $date,
                'value' => $value,
            ];
        }

        BatchInsertOrUpdate::execute('index_values', $data);
    }

    private static function getExpectedDatesAccordinglyIndex(Index $index, Carbon $start_date, Carbon $end_date): array {
        if($index->id == Index::IPCA_ID) {
            return Calendar::getWorkingDaysDatesForRange($start_date, (clone $end_date)->endOfMonth());
        }

        return Calendar::getWorkingDaysDatesForRange($start_date, $end_date);
    }

    private static function extractDatesStoredInRange(array $values_stored_in_range): array {
        $dates = [];
        foreach ($values_stored_in_range as $value) {
            $dates[] = $value['date'];
        }

        usort($dates, function ($date_1, $date_2) {
            $date_1 = Carbon::parse($date_1);
            $date_2 = Carbon::parse($date_2);

            return $date_1->lt($date_2);
        });

        return $dates;
    }

    private static function clearCache(): void {
        self::$missing_dates = null;
    }
}
