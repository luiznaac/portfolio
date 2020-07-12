<?php

namespace Tests\Portfolio\API;

use App\Model\Stock\StockType;
use Carbon\Carbon;
use Tests\TestCase;

abstract class DividendAPITest extends TestCase {

    public function dataProviderTestGetDividendsForRange(): array {
        return [
            'SQIA3' => [
                'symbol' => 'SQIA3',
                'start_date' => Carbon::parse('2015-10-22'),
                'end_date' => Carbon::parse('2016-05-11'),
                'expected_dividends' => [
                    '2015-10-22|2015-10-09|JCP' => 0.11580000,
                    '2015-10-22|2015-10-12|JCP' => 0.11576016,
                    '2015-12-23|2015-12-11|JCP' => 0.11250000,
                    '2016-05-11|2016-04-29|Dividendo' => 0.00100000,
                    '2016-05-11|2016-04-29|JCP' => 0.10130000,
                ]
            ],
            'XPML11' => [
                'symbol' => 'XPML11',
                'start_date' => Carbon::parse('2020-01-20'),
                'end_date' => Carbon::parse('2020-06-27'),
                'expected_dividends' => [
                    '2020-01-24|2020-01-17|Dividendo' => 0.61000000,
                    '2020-02-21|2020-02-14|Dividendo' => 0.50000000,
                    '2020-06-25|2020-06-18|Dividendo' => 0.27000000,
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestGetDividendsForRange
     */
    abstract public function testGetDividendsForRange(string $symbol, Carbon $start_date, Carbon $end_date, array $expected_dividends): void;
}
