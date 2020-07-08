<?php

namespace Tests\Portfolio\API;

use App\Model\Stock\Stock;
use App\Model\Stock\StockType;
use App\Portfolio\API\StatusInvestAPI;
use Carbon\Carbon;

class StatusInvestAPITest extends PriceAPITest {

    public function dataProviderTestGetTypeForStock(): array {
        return [
            'SQIA3 type' => [
                'symbol' => 'SQIA3',
                'expected_type' => StockType::ACAO_TYPE,
            ],
            'BOVA11 type' => [
                'symbol' => 'BOVA11',
                'expected_type' => StockType::ETF_TYPE,
            ],
            'XPML11 type' => [
                'symbol' => 'XPML11',
                'expected_type' => StockType::FII_TYPE,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestGetTypeForStock
     */
    public function testGetTypeForStock(string $symbol, string $expected_type): void {
        $stock = Stock::getStockBySymbol($symbol);
        $prices = StatusInvestAPI::getTypeForStock($stock);

        $this->assertEquals($expected_type, $prices);
    }

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
}
