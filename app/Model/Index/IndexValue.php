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

        if(!self::hasMissingData($index->getFrequency(), $values_stored_in_range, $start_date, $end_date)) {
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

    private static function hasMissingData(string $frequency, array $values_stored_in_range, Carbon $start_date, Carbon $end_date): bool {
        $missing_dates = self::getMissingDates($frequency, $values_stored_in_range, $start_date, $end_date);

        return !empty($missing_dates);
    }

    private static function loadValuesForMissingDates(Index $index, array $values_stored_in_range, Carbon $start_date, Carbon $end_date): void {
        $missing_dates = self::getMissingDates($index->getFrequency(), $values_stored_in_range, $start_date, $end_date);
        Log::log('debug', __CLASS__.'::'.__FUNCTION__, Index::getIndexAbbr($index->id) . ' has missing values ' . implode(', ', $missing_dates));
        $start_date = Carbon::parse($missing_dates[0]);
        $end_date = Carbon::parse($missing_dates[sizeof($missing_dates)-1]);

        self::loadValuesForDatesAndStore($index, $start_date, $end_date);
    }

    private static function getMissingDates(string $frequency, array $values_stored_in_range, Carbon $start_date, Carbon $end_date): array {
        return self::$missing_dates ?? self::$missing_dates = self::calculateMissingDates($frequency, $values_stored_in_range, $start_date, $end_date);
    }

    private static function calculateMissingDates(string $frequency, array $values_stored_in_range, Carbon $start_date, Carbon $end_date): array {
        $expected_dates = self::getExpectedDatesAccordinglyIndexFrequency($frequency, $start_date, $end_date);
        $dates_stored = self::extractDatesStoredInRange($values_stored_in_range);

        return array_values(array_diff($expected_dates, $dates_stored));
    }

    private static function loadValuesForDatesAndStore(Index $index, Carbon $start_date, Carbon $end_date): void {
        $values = IndexProvider::getIndexValuesForRange($index, $start_date, $end_date);

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

    private static function getExpectedDatesAccordinglyIndexFrequency(string $frequency, Carbon $start_date, Carbon $end_date): array {
        if($frequency == 'daily') {
            return Calendar::getWorkingDaysDatesForRange($start_date, $end_date);
        }

        return Calendar::getStartOfAllMonthsForRange($start_date, $end_date);
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
