<?php

namespace App\Model\Holiday;

use App\Model\Order\Order;
use App\Portfolio\Providers\HolidayProvider;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Holiday\Holiday
 *
 * @property int $id
 * @property string $date
 * @property string $description
 */

class Holiday extends Model {

    protected $fillable = ['date', 'description'];

    private static $cached_holidays = [];

    public static function isHoliday(Carbon $date): bool {
        $holidays_in_year = self::getHolidaysForYearFromCache($date);

        return isset($holidays_in_year[$date->toDateString()]);
    }

    public static function loadHolidays(): void {
        $years_to_load = self::getYearsToBeLoaded();

        foreach ($years_to_load as $year) {
            $year_now = Carbon::createFromFormat('Y', $year);
            self::loadHolidaysForYear($year_now);
        }
    }

    private static function getHolidaysForYearFromCache(Carbon $date): array {
        if(!isset(self::$cached_holidays[$date->year])) {
            self::cacheHolidaysForYear($date);
        }

        return isset(self::$cached_holidays[$date->year]) ? self::$cached_holidays[$date->year] : [];
    }

    private static function cacheHolidaysForYear(Carbon $date) {
        $start_date = (clone $date)->startOfYear();
        $end_date = (clone $date)->endOfYear();

        $holidays = self::query()
            ->whereBetween('date', [$start_date->toDateString(), $end_date->toDateString()])->get();

        /** @var self $holiday */
        foreach ($holidays as $holiday) {
            self::$cached_holidays[$date->year][$holiday->date] = $holiday->description;
        }
    }

    private static function loadHolidaysForYear(Carbon $date): void {
        $holidays_in_year = HolidayProvider::getHolidaysForYear($date);

        foreach ($holidays_in_year as $date => $holiday) {
            self::query()->create([
                'date' => $date,
                'description' => $holiday,
            ]);
        }
    }

    private static function getYearsToBeLoaded(): array {
        $oldest_order_year = self::getOldestOrderYear();

        if(!$oldest_order_year) {
            return [];
        }

        $years_stored = self::getYearsStored();
        $expected_years = Calendar::getYearsForRange(
            Carbon::createFromFormat('Y', $oldest_order_year),
            Carbon::today()
        );

        return array_diff($expected_years, $years_stored);
    }

    private static function getOldestOrderYear(): ?int {
        return Order::query()
            ->selectRaw('YEAR(MIN(date)) AS min_year')
            ->get()->toArray()[0]['min_year'];
    }

    private static function getYearsStored(): array {
        return Holiday::query()
            ->selectRaw('DISTINCT YEAR(date) AS holiday_year')
            ->get()->pluck('holiday_year')->toArray();
    }
}
