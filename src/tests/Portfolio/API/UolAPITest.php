<?php

namespace Tests\Portfolio\API;

use App\Portfolio\API\UolAPI;
use Carbon\Carbon;
use Tests\TestCase;

class UolAPITest extends TestCase {

    public function testGetStockPriceForDateRange(): void {
        $start_date = Carbon::parse('2020-06-22');
        $end_date = Carbon::parse('2020-06-24');
        $prices = UolAPI::getStockPricesForRange('FLRY3', $start_date, $end_date);

        $this->assertEquals(
            [
                '2020-06-22' => 24.8,
                '2020-06-23' => 25.15,
                '2020-06-24' => 24.56,
            ],
            $prices);
    }

    public function testGetStockPriceForDateOnHoliday_ShouldGetLastAvailablePrice(): void {
        $holiday_date = Carbon::createFromFormat('Y-m-d', '2018-07-09');
        $price = UolAPI::getStockPriceForDate('XPML11', $holiday_date);

        $this->assertEquals(98.5, $price);
    }

    public function testGetSymbolTest(): void {
        $date = Carbon::createFromFormat('Y-m-d', '2020-06-22');
        $price = UolAPI::getStockPriceForDate('FLRY3', $date);

        $this->assertEquals(24.8, $price);
    }

    public function testGetStockPriceForDateOnWeekend_ShouldGetFridayPrice(): void {
        $weekend_date = Carbon::createFromFormat('Y-m-d', '2020-06-21');
        $price = UolAPI::getStockPriceForDate('ITSA4', $weekend_date);

        $this->assertEquals(10.41, $price);
    }
}
