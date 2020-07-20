<?php

namespace Tests\Model\Stock\Dividend;

use App\Model\Order\Order;
use App\Model\Stock\Stock;
use App\Model\Stock\Dividend\StockDividend;
use Carbon\Carbon;
use Tests\TestCase;

class StockDividendTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        $this->loginWithFakeUser();
    }

    public function testLoadHistoricDividendsForAllStocksWithFII_ShouldLoadValues(): void {
        $stock = Stock::getStockBySymbol('XPML11');
        Carbon::setTestNow('2020-06-25 21:00:00');
        $this->createOrder($stock, '2019-01-04');

        $expected_data = [
            ['stock_id' => $stock->id, 'type' => 'Dividendo', 'date_paid' => '2020-01-24', 'reference_date' => '2020-01-17', 'value' => 0.61000000],
            ['stock_id' => $stock->id, 'type' => 'Dividendo', 'date_paid' => '2020-02-21', 'reference_date' => '2020-02-14', 'value' => 0.50000000],
            ['stock_id' => $stock->id, 'type' => 'Dividendo', 'date_paid' => '2020-06-25', 'reference_date' => '2020-06-18', 'value' => 0.27000000],
        ];

        StockDividend::loadHistoricDividendsForAllStocks();
        $stock_dividends = StockDividend::getStockDividendsStoredInDatePaidRange($stock,  Carbon::today()->subMonths(6), Carbon::today());

        $this->assertStockDividends($expected_data, $stock_dividends);
    }

    public function testLoadHistoricDividendsForAllStocksWithAcao_ShouldLoadValues(): void {
        $stock = Stock::getStockBySymbol('FLRY3');
        Carbon::setTestNow('2019-10-04 21:00:00');
        $this->createOrder($stock, '2019-04-04');

        $expected_data = [
            ['stock_id' => $stock->id, 'type' => 'Dividendo', 'date_paid' => '2019-05-31', 'reference_date' => '2019-03-06', 'value' => 0.68740000],
            ['stock_id' => $stock->id, 'type' => 'JCP', 'date_paid' => '2019-10-04', 'reference_date' => '2019-07-30', 'value' => 0.20030000],
        ];

        StockDividend::loadHistoricDividendsForAllStocks();
        $stock_dividends = StockDividend::getStockDividendsStoredInDatePaidRange($stock,  Carbon::today()->subMonths(6), Carbon::today());

        $this->assertStockDividends($expected_data, $stock_dividends);
    }

    private function createOrder(Stock $stock, string $date): void {
        $order_1 = new Order();
        $order_1->store(
            $stock,
            Carbon::parse($date),
            $type = 'buy',
            $quantity = 10,
            $price = 15.22,
            $cost = 7.50
        );
    }

    private function assertStockDividends(array $expected_dividends, array $actual_dividends): void {
        $this->assertCount(sizeof($expected_dividends), $actual_dividends);
        ksort($expected_dividends);

        foreach ($actual_dividends as $actual_dividend) {
            $expected_dividend = array_shift($expected_dividends);

            $this->assertEquals($expected_dividend['stock_id'], $actual_dividend['stock_id']);
            $this->assertEquals($expected_dividend['type'], $actual_dividend['type']);
            $this->assertEquals($expected_dividend['date_paid'], $actual_dividend['date_paid']);
            $this->assertEquals($expected_dividend['reference_date'], $actual_dividend['reference_date']);
        }
    }
}
