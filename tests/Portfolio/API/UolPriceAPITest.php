<?php

namespace Tests\Portfolio\API;

use App\Model\Stock\Stock;
use App\Portfolio\API\UolAPI;
use Carbon\Carbon;

class UolPriceAPITest extends PriceAPITest {

    /**
     * @dataProvider dataProviderForTestGetPricesForRange
     */
    public function testGetPricesForRange(string $symbol, Carbon $start_date, Carbon $end_date, array $expected_prices): void {
        $stock = Stock::getStockBySymbol($symbol);
        $prices = UolAPI::getPricesForRange($stock, $start_date, $end_date);

        $this->assertEquals($expected_prices, $prices);
    }

    /**
     * @dataProvider dataProviderForTestGetPriceForDate
     */
    public function testGetPriceForDate(string $symbol, Carbon $date, float $expected_price): void {
        $stock = Stock::getStockBySymbol($symbol);
        $price = UolAPI::getPriceForDate($stock, $date);

        $this->assertEquals($expected_price, $price);
    }
}
