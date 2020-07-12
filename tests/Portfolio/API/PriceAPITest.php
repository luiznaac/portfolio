<?php

namespace Tests\Portfolio\API;

use Carbon\Carbon;
use Tests\TestCase;

abstract class PriceAPITest extends TestCase {

    /**
     * @dataProvider dataProviderForTestGetPricesForRange
     */
    abstract public function testGetPricesForRange(string $symbol, Carbon $start_date, Carbon $end_date, array $expected_prices): void;

    /**
     * @dataProvider dataProviderForTestGetPriceForDate
     */
    abstract public function testGetPriceForDate(string $symbol, Carbon $date, float $expected_price): void;

    public function dataProviderForTestGetPricesForRange(): array {
        return [
            'Date range with one date - should return only one date' => [
                'symbol' => 'XPML11',
                'start_date' => Carbon::parse('2020-06-16'),
                'end_date' => Carbon::parse('2020-06-16'),
                'expected_prices' => [
                    '2020-06-16' => 107.16,
                ]
            ],
            'SQIA3 prices' => [
                'symbol' => 'SQIA3',
                'start_date' => Carbon::parse('2020-06-15'),
                'end_date' => Carbon::parse('2020-06-19'),
                'expected_prices' => [
                    '2020-06-19' => 19.5,
                    '2020-06-18' => 19.4,
                    '2020-06-17' => 19.15,
                    '2020-06-16' => 18.98,
                    '2020-06-15' => 19.38,
                ]
            ],
            'BOVA11 prices' => [
                'symbol' => 'BOVA11',
                'start_date' => Carbon::parse('2020-06-15'),
                'end_date' => Carbon::parse('2020-06-19'),
                'expected_prices' => [
                    '2020-06-19' => 92.64,
                    '2020-06-18' => 92.63,
                    '2020-06-17' => 91.95,
                    '2020-06-16' => 90.0,
                    '2020-06-15' => 89.07,
                ]
            ],
        ];
    }

    public function dataProviderForTestGetPriceForDate(): array {
        return [
            'SQIA3 price' => [
                'symbol' => 'SQIA3',
                'date' => Carbon::parse('2020-06-24'),
                'expected_price' => 18.77,
            ],
            'BOVA11 price' => [
                'symbol' => 'BOVA11',
                'date' => Carbon::parse('2020-06-24'),
                'expected_price' => 90.77,
            ],
        ];
    }
}
