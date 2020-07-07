<?php

namespace Tests\Portfolio\API;

use App\Model\Stock\Stock;
use App\Portfolio\API\UolAPI;
use Carbon\Carbon;
use Tests\TestCase;

class UolAPITest extends TestCase {

    public function testGetPricesForRange(): void {
        $stock = Stock::getStockBySymbol('SQIA3');
        $start_date = Carbon::parse('2020-06-15');
        $end_date = Carbon::parse('2020-06-19');
        $prices = UolAPI::getPricesForRange($stock, $start_date, $end_date);

        $this->assertEquals(
            [
                '2020-06-19' => 19.5,
                '2020-06-18' => 19.4,
                '2020-06-17' => 19.15,
                '2020-06-16' => 18.98,
                '2020-06-15' => 19.38,
            ],
            $prices);
    }

    public function testGetPriceForDate(): void {
        $stock = Stock::getStockBySymbol('BOVA11');
        $date = Carbon::parse('2020-06-24');
        $price = UolAPI::getPriceForDate($stock, $date);

        $this->assertEquals(90.77, $price);
    }
}
