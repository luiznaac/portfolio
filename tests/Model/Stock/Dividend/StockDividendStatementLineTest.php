<?php

namespace Tests\Model\Stock\Dividend;

use App\Model\Order\Order;
use App\Model\Stock\Dividend\StockDividendStatementLine;
use App\Model\Stock\Stock;
use App\Model\Stock\Dividend\StockDividend;
use Carbon\Carbon;
use Tests\TestCase;

class StockDividendStatementLineTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        $this->loginWithFakeUser();
    }

    public function testGetOldestDateOfMissingStockDividendStatementLineForEachStock(): void {
        $orders = [
            ['stock_symbol' => 'MALL11', 'date' => '2019-09-19', 'type' => 'buy', 'quantity' => 10, 'price' => 102.5, 'cost' => 0],
            ['stock_symbol' => 'KNCR11', 'date' => '2019-09-19', 'type' => 'buy', 'quantity' => 10, 'price' => 102.5, 'cost' => 0],
        ];

        $stock_dividends = [
            ['stock_symbol' => 'MALL11', 'type' => 'Dividendo', 'date_paid' => '2020-07-20', 'reference_date' => '2020-07-13', 'value' => 0.5],
            ['stock_symbol' => 'KNCR11', 'type' => 'Dividendo', 'date_paid' => '2020-07-20', 'reference_date' => '2020-07-15', 'value' => 0.6],
            ['stock_symbol' => 'VISC11', 'type' => 'Dividendo', 'date_paid' => '2020-07-20', 'reference_date' => '2020-07-15', 'value' => 0.6],
        ];

        $this->saveOrders($orders);
        $this->saveStockDividends($stock_dividends);

        Carbon::setTestNow('2020-07-20 21:00:00');

        $this->assertEquals(
            [
                Stock::getStockBySymbol('MALL11')->id => '2020-07-13',
                Stock::getStockBySymbol('KNCR11')->id => '2020-07-15',
            ],
            StockDividendStatementLine::getOldestDateOfMissingStockDividendStatementLineForEachStock()
        );
    }
}
