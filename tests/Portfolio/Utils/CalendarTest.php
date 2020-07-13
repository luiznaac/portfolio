<?php

namespace Tests\Portfolio\Utils;

use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Tests\TestCase;

class CalendarTest extends TestCase {

    public function dataProviderForTestGetWorkingDaysDatesForRange(): array {
        return [
            'Start date - working day and End date - working day' => [
                'start_date' => '2020-07-01',
                'end_date' => '2020-07-07',
                'expected_dates' => [
                    '2020-07-01', '2020-07-02', '2020-07-03', '2020-07-06', '2020-07-07',
                ],
            ],
            'Start date - working day and End date - weekend' => [
                'start_date' => '2020-07-01',
                'end_date' => '2020-07-05',
                'expected_dates' => [
                    '2020-07-01', '2020-07-02', '2020-07-03',
                ],
            ],
            'Start date - weekend and End date - weekend day' => [
                'start_date' => '2020-06-27',
                'end_date' => '2020-07-05',
                'expected_dates' => [
                    '2020-06-29', '2020-06-30','2020-07-01', '2020-07-02', '2020-07-03',
                ],
            ],
            'Start date - weekend and End date - working day' => [
                'start_date' => '2020-06-27',
                'end_date' => '2020-07-07',
                'expected_dates' => [
                    '2020-06-29', '2020-06-30', '2020-07-01', '2020-07-02', '2020-07-03', '2020-07-06', '2020-07-07',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetWorkingDaysDatesForRange
     */
    public function testGetWorkingDaysDatesForRange(string $start_date, string $end_date, array $expected_dates): void {
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);

        $actual_dates = Calendar::getWorkingDaysDatesForRange($start_date, $end_date);

        $this->assertEquals($expected_dates, $actual_dates);
    }

    public function dataProviderForTestGetLastMarketWorkingDate(): array {
        return [
            'Now as Sunday' => [
                'now' => '2020-07-12 12:00:00',
                'expected_date' => Carbon::parse('2020-07-10'),
            ],
            'Now as Saturday' => [
                'now' => '2020-07-11 12:00:00',
                'expected_date' => Carbon::parse('2020-07-10'),
            ],
            'Now as Monday pre market close' => [
                'now' => '2020-07-13 16:30:00',
                'expected_date' => Carbon::parse('2020-07-10'),
            ],
            'Now as Monday post market close' => [
                'now' => '2020-07-13 18:00:00',
                'expected_date' => Carbon::parse('2020-07-13'),
            ],
            'Now as weekday pre market close' => [
                'now' => '2020-07-09 17:00:00',
                'expected_date' => Carbon::parse('2020-07-08'),
            ],
            'Now as weekday post market close' => [
                'now' => '2020-07-09 18:00:00',
                'expected_date' => Carbon::parse('2020-07-09'),
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetLastMarketWorkingDate
     */
    public function testGetLastMarketWorkingDate(string $now, Carbon $expected_date): void {
        $now_in_utc = Carbon::parse($now, Calendar::B3_TIMEZONE)->utc();
        Carbon::setTestNow($now_in_utc);

        $actual_date = Calendar::getLastMarketWorkingDate();

        $this->assertEquals($expected_date, $actual_date);
    }

    public function testGetLastWorkingDayForDateOnWeekDay_ShouldReturnSameDay(): void {
        $date = Carbon::parse('2020-07-01');
        $working_day = Calendar::getLastWorkingDayForDate($date);

        $this->assertEquals('2020-07-01', $working_day->toDateString());
    }

    public function testGetLastWorkingDayForDateOnWeekend_ShouldReturnFriday(): void {
        $date = Carbon::parse('2020-07-05');
        $working_day = Calendar::getLastWorkingDayForDate($date);

        $this->assertEquals('2020-07-03', $working_day->toDateString());
    }

    public function testGetLastWorkingDayOnWeekDay_ShouldReturnSameDay(): void {
        Carbon::setTestNow('2020-06-16');
        $working_day = Calendar::getLastWorkingDay();

        $this->assertEquals('2020-06-16', $working_day->toDateString());
    }

    public function testGetLastWorkingDayOnWeekend_ShouldReturnFriday(): void {
        Carbon::setTestNow('2020-07-05');
        $working_day = Calendar::getLastWorkingDay();

        $this->assertEquals('2020-07-03', $working_day->toDateString());
    }
}
