<?php

namespace Tests\Portfolio\Consolidator;

use App\Portfolio\Consolidator\ConsolidatorDateProvider;
use Carbon\Carbon;
use Tests\TestCase;

class ConsolidatorDateProviderTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        $this->loginWithFakeUser();
    }

    /**
     * @dataProvider dataProviderForTestGetStockDividendDatesToBeUpdated
     */
    public function testGetStockDividendDatesToBeUpdated(string $now, array $stock_dividends, array $dividend_lines, array $orders, array $stock_positions, array $expected_dates): void {
        Carbon::setTestNow($now);
        $this->saveStockDividends($stock_dividends);
        $this->saveDividendLines($dividend_lines);
        $this->saveOrders($orders);
        $this->saveStockPositions($stock_positions);
        $this->translateStockSymbolsToIdsForDates($expected_dates);

        $dividend_dates = ConsolidatorDateProvider::getStockDividendDatesToBeUpdated();

        $this->assertEquals($expected_dates, $dividend_dates);
    }

    /**
     * @dataProvider dataProviderForTestGetStockDatesToBeUpdated
     */
    public function testGetStockPositionDatesToBeUpdated(string $now, array $stock_positions, array $orders, array $expected_dates): void {
        Carbon::setTestNow($now);
        $this->saveStockPositions($stock_positions);
        $this->saveOrders($orders);
        $this->translateStockSymbolsToIdsForDates($expected_dates);

        $stock_dates = ConsolidatorDateProvider::getStockPositionDatesToBeUpdated();

        $this->assertEquals($expected_dates, $stock_dates);
    }

    public function dataProviderForTestGetStockDividendDatesToBeUpdated(): array {
        return [
            'Normal scenario' => [
                'now' => '2020-06-18 21:00:00',
                'stock_dividends' => [
                    ['stock_symbol' => 'KNCR11', 'type' => 'Dividendo', 'date_paid' => '2020-06-16', 'reference_date' => '2020-06-15', 'value' => 0.6],
                    ['stock_symbol' => 'KNCR11', 'type' => 'Dividendo', 'date_paid' => '2020-06-18', 'reference_date' => '2020-06-17', 'value' => 0.6],
                ],
                'dividend_lines' => [
                    ['stock_symbol' => 'KNCR11', 'reference_date' => '2020-06-15', 'quantity' => 10, 'amount_paid' => 6],
                ],
                'orders' => [
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-12', 'type' => 'buy', 'quantity' => 10, 'price' => 102.5, 'cost' => 0],
                ],
                'stock_positions' => [
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-12', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-15', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-16', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-17', 'updated_at' => '2020-07-09 23:01:00'],
                ],
                'expected_dates' => [
                    'KNCR11' => '2020-06-17',
                ],
            ],
            'Normal scenario and no new dividend' => [
                'now' => '2020-06-18 21:00:00',
                'stock_dividends' => [
                    ['stock_symbol' => 'KNCR11', 'type' => 'Dividendo', 'date_paid' => '2020-06-16', 'reference_date' => '2020-06-15', 'value' => 0.6],
                    ['stock_symbol' => 'KNCR11', 'type' => 'Dividendo', 'date_paid' => '2020-06-18', 'reference_date' => '2020-06-17', 'value' => 0.6],
                ],
                'dividend_lines' => [
                    ['stock_symbol' => 'KNCR11', 'reference_date' => '2020-06-15', 'quantity' => 10, 'amount_paid' => 6],
                    ['stock_symbol' => 'KNCR11', 'reference_date' => '2020-06-17', 'quantity' => 10, 'amount_paid' => 6],
                ],
                'orders' => [
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-12', 'type' => 'buy', 'quantity' => 10, 'price' => 102.5, 'cost' => 0],
                ],
                'stock_positions' => [
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-12', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-15', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-16', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-17', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-18', 'updated_at' => '2020-07-09 23:01:00'],
                ],
                'expected_dates' => [],
            ],
            'Normal scenario and with new dividend' => [
                'now' => '2020-06-18 21:00:00',
                'stock_dividends' => [
                    ['stock_symbol' => 'KNCR11', 'type' => 'Dividendo', 'date_paid' => '2020-06-16', 'reference_date' => '2020-06-15', 'value' => 0.6],
                    ['stock_symbol' => 'KNCR11', 'type' => 'Dividendo', 'date_paid' => '2020-06-18', 'reference_date' => '2020-06-17', 'value' => 0.6],
                ],
                'dividend_lines' => [
                    ['stock_symbol' => 'KNCR11', 'reference_date' => '2020-06-15', 'quantity' => 10, 'amount_paid' => 6],
                ],
                'orders' => [
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-12', 'type' => 'buy', 'quantity' => 10, 'price' => 102.5, 'cost' => 0],
                ],
                'stock_positions' => [
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-12', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-15', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-16', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-17', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'KNCR11', 'date' => '2020-06-18', 'updated_at' => '2020-07-09 23:01:00'],
                ],
                'expected_dates' => [
                    'KNCR11' => '2020-06-17',
                ],
            ],
        ];
    }

    public function dataProviderForTestGetStockDatesToBeUpdated(): array {
        return [
            'Everything is updated - should return empty array' => [
                'now' => '2020-07-09 23:00:00',
                'stock_positions' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-09', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-09', 'updated_at' => '2020-07-09 23:01:00'],
                ],
                'orders' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-01', 'updated_at' => '2020-07-09 23:00:00', 'type' => 'buy'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-01', 'updated_at' => '2020-07-09 23:00:00', 'type' => 'buy'],
                ],
                'expected_dates' => [],
            ],
            'One stock position is outdated - should return stock position date' => [
                'now' => '2020-07-09 23:00:00',
                'stock_positions' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-08', 'updated_at' => '2020-07-08 23:01:00'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-09', 'updated_at' => '2020-07-09 23:01:00'],
                ],
                'orders' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-01', 'updated_at' => '2020-07-08 23:00:00', 'type' => 'buy'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-01', 'updated_at' => '2020-07-09 23:00:00', 'type' => 'buy'],
                ],
                'expected_dates' => [
                    'SQIA3' => '2020-07-08',
                ],
            ],
            'Two stock positions are outdated - should return stock positions dates' => [
                'now' => '2020-07-09 23:00:00',
                'stock_positions' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-08', 'updated_at' => '2020-07-08 23:01:00'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-07', 'updated_at' => '2020-07-07 23:01:00'],
                ],
                'orders' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-01', 'updated_at' => '2020-07-07 23:00:00', 'type' => 'buy'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-01', 'updated_at' => '2020-07-07 23:00:00', 'type' => 'buy'],
                ],
                'expected_dates' => [
                    'SQIA3' => '2020-07-08',
                    'XPML11' => '2020-07-07',
                ],
            ],
            'Positions updated but has an order before last reference date - should return order date' => [
                'now' => '2020-07-09 23:00:00',
                'stock_positions' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-09', 'updated_at' => '2020-07-09 23:01:00'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-09', 'updated_at' => '2020-07-09 23:01:00'],
                ],
                'orders' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-01', 'updated_at' => '2020-07-09 23:02:00', 'type' => 'buy'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-01', 'updated_at' => '2020-07-09 23:00:00', 'type' => 'buy'],
                ],
                'expected_dates' => [
                    'SQIA3' => '2020-07-01',
                ],
            ],
            'Positions outdated and has an order before last reference date - should return order date' => [
                'now' => '2020-07-09 23:00:00',
                'stock_positions' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-07', 'updated_at' => '2020-07-07 23:01:00'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-09', 'updated_at' => '2020-07-09 23:01:00'],
                ],
                'orders' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-01', 'updated_at' => '2020-07-09 23:02:00', 'type' => 'buy'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-01', 'updated_at' => '2020-07-09 23:00:00', 'type' => 'buy'],
                ],
                'expected_dates' => [
                    'SQIA3' => '2020-07-01',
                ],
            ],
            'Positions outdated and has an order after last reference date - should return position date' => [
                'now' => '2020-07-09 23:00:00',
                'stock_positions' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-07', 'updated_at' => '2020-07-07 23:01:00'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-09', 'updated_at' => '2020-07-09 23:01:00'],
                ],
                'orders' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-08', 'updated_at' => '2020-07-09 23:02:00', 'type' => 'buy'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-01', 'updated_at' => '2020-07-09 23:00:00', 'type' => 'buy'],
                ],
                'expected_dates' => [
                    'SQIA3' => '2020-07-07',
                ],
            ],
            'No positions for stock but with order - should return order date' => [
                'now' => '2020-07-09 23:00:00',
                'stock_positions' => [
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-09', 'updated_at' => '2020-07-09 23:01:00'],
                ],
                'orders' => [
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-07-08', 'updated_at' => '2020-07-09 23:02:00', 'type' => 'buy'],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-07-01', 'updated_at' => '2020-07-09 23:00:00', 'type' => 'buy'],
                ],
                'expected_dates' => [
                    'SQIA3' => '2020-07-08',
                ],
            ],
        ];
    }
}
