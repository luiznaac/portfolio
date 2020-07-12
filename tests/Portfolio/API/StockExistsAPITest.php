<?php

namespace Tests\Portfolio\API;

use Carbon\Carbon;
use Tests\TestCase;

abstract class StockExistsAPITest extends TestCase {

    /**
     * @dataProvider dataProviderForTestCheckIfSymbolIsValid
     */
    abstract public function testCheckIfSymbolIsValid(string $symbol, bool $expected_answer): void;

    public function dataProviderForTestCheckIfSymbolIsValid(): array {
        return [
            'Valid Stock 1' => [
                'symbol' => 'XPML11',
                'expected_answer' => true,
            ],
            'Valid Stock 2' => [
                'symbol' => 'IVVB11',
                'expected_answer' => true,
            ],
            'Valid Stock 3' => [
                'symbol' => 'ITSA4',
                'expected_answer' => true,
            ],
            'Invalid Stock but possible search' => [
                'symbol' => 'XP',
                'expected_answer' => false,
            ],
            'Invalid Stock' => [
                'symbol' => 'XXXXX',
                'expected_answer' => false,
            ],
        ];
    }
}
