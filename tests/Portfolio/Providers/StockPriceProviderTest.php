<?php

namespace Tests\Portfolio\Providers;

use App\Model\Stock\Stock;
use App\Portfolio\Providers\StockPriceProvider;
use Carbon\Carbon;
use Tests\TestCase;

class StockPriceProviderTest extends TestCase {

    public function testGetPricesForRange(): void {
        $stock = new Stock();
        $stock->symbol = 'IVVB11';
        $stock->save();

        $start_date = Carbon::parse('2020-07-02');
        $end_date = Carbon::parse('2020-07-06');

        $prices = StockPriceProvider::getPricesForRange($stock, $start_date, $end_date);

        $this->assertEquals([
                '2020-07-02' => 181.24,
                '2020-07-03' => 182.00,
                '2020-07-06' => 183.45,
            ],
            $prices);
    }

    public function testGetPriceForDate(): void {
        $stock = new Stock();
        $stock->symbol = 'IVVB11';
        $stock->save();

        $price = StockPriceProvider::getPriceForDate($stock, Carbon::parse('2020-07-07'));

        $this->assertEquals(182.60, $price);
    }
}
