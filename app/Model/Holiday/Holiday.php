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

    public static function loadHolidays(): void {
        $years_to_load = self::getYearsToBeLoaded();

        foreach ($years_to_load as $year) {
            $year_now = Carbon::createFromFormat('Y', $year);
            self::loadHolidaysForYear($year_now);
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
