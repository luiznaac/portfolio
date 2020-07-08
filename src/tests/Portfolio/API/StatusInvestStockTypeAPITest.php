<?php

namespace Tests\Portfolio\API;

use App\Model\Stock\Stock;
use App\Portfolio\API\StatusInvestAPI;

class StatusInvestStockTypeAPITest extends StockTypeAPITest {

    /**
     * @dataProvider dataProviderTestGetTypeForStock
     */
    public function testGetTypeForStock(string $symbol, string $expected_type): void {
        $stock = Stock::getStockBySymbol($symbol);
        $prices = StatusInvestAPI::getTypeForStock($stock);

        $this->assertEquals($expected_type, $prices);
    }
}
