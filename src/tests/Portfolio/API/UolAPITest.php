<?php

namespace Tests\Portfolio\API;

use App\Portfolio\API\UolAPI;
use Carbon\Carbon;
use Tests\TestCase;

class UolAPITest extends TestCase {

    public function testGetSymbolTest(): void {
        $date = Carbon::createFromFormat('Y-m-d', '2020-06-22');
        $price = UolAPI::getStockPriceForDate('FLRY3', $date);

        $this->assertEquals(24.8, $price);
    }

    public function testGetSymbolTestOnWeekend_ShouldGetFridayPrice(): void {
        $weekend_date = Carbon::createFromFormat('Y-m-d', '2020-06-21');
        $price = UolAPI::getStockPriceForDate('ITSA4', $weekend_date);

        $this->assertEquals(10.41, $price);
    }
}
