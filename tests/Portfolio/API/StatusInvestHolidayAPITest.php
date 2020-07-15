<?php

namespace Tests\Portfolio\API;

use App\Portfolio\API\StatusInvestAPI;
use Carbon\Carbon;
use Tests\Portfolio\API\Abstracts\HolidayAPITest;

class StatusInvestHolidayAPITest extends HolidayAPITest {

    /**
     * @dataProvider dataProviderForTestGetHolidaysForYear
     */
    public function testGetGetHolidaysForYear(string $year, array $expected_holidays): void {
        $date = Carbon::createFromFormat('Y', $year);
        $holidays = StatusInvestAPI::getHolidaysForYear($date);

        $this->assertCount(sizeof($expected_holidays), $holidays);
        $this->assertEquals($expected_holidays, $holidays);
    }
}
