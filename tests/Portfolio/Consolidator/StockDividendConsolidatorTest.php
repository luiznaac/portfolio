<?php

namespace Tests\Portfolio\Consolidator;

use App\Model\Order\Order;
use App\Model\Stock\Dividend\StockDividendStatementLine;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\StockDividendConsolidator;
use App\Portfolio\Consolidator\StockPositionConsolidator;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Tests\TestCase;

class StockDividendConsolidatorTest extends TestCase {

    private $user;

    protected function setUp(): void {
        parent::setUp();
        $this->loginWithFakeUser();
        Carbon::setTestNow('2020-06-30');

        $stock_1 = Stock::getStockBySymbol('SQIA3');

        $order_1 = new Order();
        $order_1->store(
            $stock_1,
            Carbon::now()->subDays(2),
            $type = 'buy',
            $quantity = 10,
            $price = 18.22,
            $cost = 7.50
        );

        $stock_2 = Stock::getStockBySymbol('XPML11');
        Carbon::setTestNow('2020-06-29');

        $order_1 = new Order();
        $order_1->store(
            $stock_1,
            Carbon::now()->subDays(3),
            $type = 'buy',
            $quantity = 10,
            $price = 15.22,
            $cost = 7.50
        );

        $order_2 = new Order();
        $order_2->store(
            $stock_2,
            Carbon::now()->subDays(3),
            $type = 'buy',
            $quantity = 10,
            $price = 15.22,
            $cost = 7.50
        );

        Carbon::setTestNow('2020-06-24');

        $order_1 = new Order();
        $order_1->store(
            $stock_1,
            Carbon::now()->subDay(),
            $type = 'buy',
            $quantity = 10,
            $price = 15.22,
            $cost = 7.50
        );

        $order_2 = new Order();
        $order_2->store(
            $stock_2,
            Carbon::now()->subDays(2),
            $type = 'buy',
            $quantity = 10,
            $price = 15.22,
            $cost = 7.50
        );

        $stock_3 = Stock::getStockBySymbol('BOVA11');

        $order_1 = new Order();
        $order_1->store(
            $stock_3,
            Carbon::now()->subDays(2),
            $type = 'buy',
            $quantity = 10,
            $price = 15.22,
            $cost = 7.50
        );

        $this->user = $this->loginWithFakeUser();
    }

    public function dataProviderForTestConsolidate(): array {
        return [
            'Dividend paid - should create line' => [
                'now' => '2019-09-25 18:00:00',
                'dividend_lines' => [],
                'orders' => [
                    ['stock_symbol' => 'XPML11', 'date' => '2019-09-18', 'type' => 'buy', 'quantity' => 10, 'price' => 102.5, 'cost' => 0],
                ],
                'expected_dividend_lines' => [
                    ['stock_dividend_id' => 1, 'quantity' => 10, 'amount_paid' => 5.7],
                ],
            ],
            'Dividends paid - should create lines' => [
                'now' => '2019-10-25 18:01:00',
                'dividend_lines' => [],
                'orders' => [
                    ['stock_symbol' => 'XPML11', 'date' => '2019-09-18', 'type' => 'buy', 'quantity' => 10, 'price' => 102.5, 'cost' => 0],
                    ['stock_symbol' => 'XPML11', 'date' => '2019-09-19', 'type' => 'buy', 'quantity' => 10, 'price' => 102.5, 'cost' => 0],
                ],
                'expected_dividend_lines' => [
                    ['stock_dividend_id' => 1, 'quantity' => 10, 'amount_paid' => 5.7],
                    ['stock_dividend_id' => 2, 'quantity' => 20, 'amount_paid' => 11.8],
                ],
            ],
            'Without orders - should not create dividend lines' => [
                'now' => '2019-09-18 18:00:00',
                'dividend_lines' => [],
                'orders' => [],
                'expected_dividend_lines' => [],
            ],
            'Dividend not yet paid - should not create lines' => [
                'now' => '2019-09-24 18:00:00',
                'dividend_lines' => [],
                'orders' => [
                    ['stock_symbol' => 'XPML11', 'date' => '2019-09-13', 'type' => 'buy', 'quantity' => 10, 'price' => 102.5, 'cost' => 0],
                ],
                'expected_dividend_lines' => [],
            ],
            'Dividend lines without orders - should delete lines' => [
                'now' => '2019-09-24 18:00:00',
                'dividend_lines' => [
                    ['stock_dividend_id' => 1, 'quantity' => 10, 'amount_paid' => 5.7],
                    ['stock_dividend_id' => 2, 'quantity' => 10, 'amount_paid' => 5.9],
                ],
                'orders' => [
                    ['stock_symbol' => 'XPML11', 'date' => '2019-09-19', 'type' => 'buy', 'quantity' => 10, 'price' => 102.5, 'cost' => 0],
                ],
                'expected_dividend_lines' => [
                    ['stock_dividend_id' => 2, 'quantity' => 10, 'amount_paid' => 5.9],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestConsolidate
     */

    public function testConsolidate_Dividends(string $now, array $dividend_lines, array $orders, array $expected_dividend_lines): void {
        $this->setTestNowForB3DateTime($now);
        $this->saveDividendLines($dividend_lines);
        $this->saveOrders($orders);
        $this->fillUserId($expected_dividend_lines);

        StockPositionConsolidator::consolidate();
        StockDividendConsolidator::consolidate();

        $this->assertStockDividendStatementLines(array_reverse($expected_dividend_lines));
    }

    private function fillUserId(array &$expected_positions): void {
        foreach ($expected_positions as &$position) {
            $position['user_id'] = $this->user->id;
        }
    }

    private function assertStockDividendStatementLines(array $expected_dividend_lines): void {
        $created_dividend_lines = StockDividendStatementLine::getBaseQuery()
            ->whereIn('stock_dividend_id', array_map(function ($line) {
                return $line['stock_dividend_id'];
            }, $expected_dividend_lines))
            ->get();

        $this->assertCount(sizeof($expected_dividend_lines), $created_dividend_lines);
        /** @var StockDividendStatementLine $expected_dividend_line */
        foreach ($expected_dividend_lines as $expected_dividend_line) {
            /** @var StockDividendStatementLine $created_dividend_line */
            $created_dividend_line = $created_dividend_lines->pop();

            $this->assertEquals($expected_dividend_line['user_id'], $created_dividend_line->user_id);
            $this->assertEquals($expected_dividend_line['stock_dividend_id'], $created_dividend_line->stock_dividend_id);
            $this->assertEquals($expected_dividend_line['quantity'], $created_dividend_line->quantity);
            $this->assertEquals($expected_dividend_line['amount_paid'], $created_dividend_line->amount_paid);
        }
    }
}
