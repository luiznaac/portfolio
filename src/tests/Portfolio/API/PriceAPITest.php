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
            ]
        ];
    }

    public function dataProviderForTestGetPriceForDate(): array {
        return [
            'BOVA11 price' => [
                'symbol' => 'SQIA3',
                'date' => Carbon::parse('2020-06-24'),
                'expected_price' => 18.77,
            ]
        ];
    }
}
