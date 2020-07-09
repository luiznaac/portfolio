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
