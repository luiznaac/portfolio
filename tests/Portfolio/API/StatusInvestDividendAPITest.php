<?php

namespace Tests\Portfolio\API;

use App\Model\Stock\Stock;
use App\Portfolio\API\StatusInvestAPI;
use Carbon\Carbon;

class StatusInvestDividendAPITest extends DividendAPITest {

    /**
     * @dataProvider dataProviderTestGetDividendsForRange
     */
    public function testGetDividendsForRange(string $symbol, Carbon $start_date, Carbon $end_date, array $expected_dividends): void {
        $stock = Stock::getStockBySymbol($symbol);
        $dividends = StatusInvestAPI::getDividendsForRange($stock, $start_date, $end_date);

        $this->assertEquals($expected_dividends, $dividends);
    }
}
