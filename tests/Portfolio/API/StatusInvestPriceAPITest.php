<?php

namespace Tests\Portfolio\API;

use App\Model\Stock\Stock;
use App\Portfolio\API\StatusInvestAPI;
use Carbon\Carbon;
use Tests\Portfolio\API\Abstracts\PriceAPITest;

class StatusInvestPriceAPITest extends PriceAPITest {

    /**
     * @dataProvider dataProviderForTestGetPricesForRange
     */
    public function testGetPricesForRange(string $symbol, Carbon $start_date, Carbon $end_date, array $expected_prices): void {
        $stock = Stock::getStockBySymbol($symbol);
        $prices = StatusInvestAPI::getPricesForRange($stock, $start_date, $end_date);

        $this->assertEquals($expected_prices, $prices);
    }

    /**
     * @dataProvider dataProviderForTestGetPriceForDate
     */
    public function testGetPriceForDate(string $symbol, Carbon $date, float $expected_price): void {
        $stock = Stock::getStockBySymbol($symbol);
        $price = StatusInvestAPI::getPriceForDate($stock, $date);

        $this->assertEquals($expected_price, $price);
    }

    public function testGetPriceForDateWithIVVB11(): void {
        $date = Carbon::parse('2020-07-07');
        $stock = new Stock();
        $stock->symbol = 'IVVB11';
        $stock->save();

        $price = StatusInvestAPI::getPriceForDate($stock, $date);

        $this->assertEquals(182.60, $price);
    }
}
