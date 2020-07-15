<?php

namespace Tests\Portfolio\API;

use App\Model\Stock\Stock;
use App\Portfolio\API\StatusInvestAPI;
use Tests\Portfolio\API\Abstracts\StockExistsAPITest;

class StatusInvestStockExistsAPITest extends StockExistsAPITest {

    /**
     * @dataProvider dataProviderForTestCheckIfSymbolIsValid
     */
    public function testCheckIfSymbolIsValid(string $symbol, bool $expected_answer): void {
        $answer = StatusInvestAPI::checkIfSymbolIsValid($symbol);

        $this->assertEquals($expected_answer, $answer);
    }
}
