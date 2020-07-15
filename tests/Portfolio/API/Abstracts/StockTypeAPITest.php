<?php

namespace Tests\Portfolio\API\Abstracts;

use App\Model\Stock\StockType;
use Tests\TestCase;

abstract class StockTypeAPITest extends TestCase {

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
    abstract public function testGetTypeForStock(string $symbol, string $expected_type): void;
}
