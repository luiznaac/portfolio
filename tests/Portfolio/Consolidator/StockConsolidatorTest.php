<?php

namespace Tests\Portfolio\Consolidator;

use App\Model\Order\Order;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\StockConsolidator;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Tests\TestCase;

class StockConsolidatorTest extends TestCase {

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

    /**
     * @dataProvider dataProviderForTestGetStockDatesToBeUpdated
     */

    public function testGetStockDatesToBeUpdated(string $now, array $stock_positions, array $orders, array $expected_dates): void {
        Carbon::setTestNow($now);
        $this->saveStockPositions($stock_positions);
        $this->saveOrders($orders);
        $this->translateStockSymbolsToIdsForDates($expected_dates);

        $stock_dates = StockConsolidator::getStockDatesToBeUpdated();

        $this->assertEquals($expected_dates, $stock_dates);
    }

    public function dataProviderForTestConsolidate(): array {
        return [
            'Without orders - should not create positions' => [
                'now' => '2020-07-03 15:00:00',
                'stock_positions' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-30', 'quantity' => 15, 'amount' => 1374.3, 'contributed_amount' => 1368.3, 'average_price' => 91.22],
                ],
                'orders' => [],
                'expected_positions' => [],
            ],
            'No orders for already consolidated stock - should delete positions' => [
                'now' => '2020-06-30 18:01:00',
                'stock_positions' => [],
                'orders' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-11', 'type' => 'buy', 'quantity' => 10, 'price' => 90.22, 'cost' => 7.50],
                ],
                'expected_positions' => [],
            ],
            'Price not found (not registered holiday) - should not create position' => [
                'now' => '2020-06-30 18:01:00',
                'stock_positions' => [],
                'orders' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-11', 'type' => 'buy', 'quantity' => 10, 'price' => 90.22, 'cost' => 7.50],
                ],
                'expected_positions' => [],
            ],
            'Already consolidated position but with new order - should update position' => [
                'now' => '2020-06-30 18:01:00',
                'stock_positions' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-30', 'quantity' => 15, 'amount' => 1374.3, 'contributed_amount' => 1368.3, 'average_price' => 91.22, 'updated_at' => '2020-06-30 18:00:00'],
                ],
                'orders' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-30', 'type' => 'buy', 'quantity' => 10, 'price' => 90.22, 'cost' => 7.50],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-30', 'type' => 'buy', 'quantity' => 5, 'price' => 90.22, 'cost' => 7.50],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-30', 'type' => 'buy', 'quantity' => 8, 'price' => 90.22, 'cost' => 7.50],
                ],
                'expected_positions' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-30', 'quantity' => 23, 'amount' => 2107.26, 'contributed_amount' => 2097.56, 'average_price' => 91.20],
                ],
            ],
            'Before market close and new stock in portfolio - should create from order date until previous market date' => [
                'now' => '2020-07-03 15:00:00',
                'stock_positions' => [],
                'orders' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-26', 'type' => 'buy', 'quantity' => 10, 'price' => 90.22, 'cost' => 7.50],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-26', 'type' => 'buy', 'quantity' => 5, 'price' => 90.22, 'cost' => 7.50],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-07-01', 'type' => 'buy', 'quantity' => 8, 'price' => 90.22, 'cost' => 7.50],
                ],
                'expected_positions' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-26', 'quantity' => 15, 'amount' => 1353.3, 'contributed_amount' => 1368.3, 'average_price' => 91.22],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-29', 'quantity' => 15, 'amount' => 1384.5, 'contributed_amount' => 1368.3, 'average_price' => 91.22],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-30', 'quantity' => 15, 'amount' => 1374.3, 'contributed_amount' => 1368.3, 'average_price' => 91.22],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-07-01', 'quantity' => 23, 'amount' => 2131.64, 'contributed_amount' => 2097.56, 'average_price' => 91.20],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-07-02', 'quantity' => 23, 'amount' => 2127.5, 'contributed_amount' => 2097.56, 'average_price' => 91.20],
                ],
            ],
            'Consolidate on weekend - should create positions until last working day' => [
                'now' => '2020-06-28 15:00:00',
                'stock_positions' => [],
                'orders' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-26', 'type' => 'buy', 'quantity' => 10, 'price' => 90.22, 'cost' => 7.50],
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-06-26', 'type' => 'buy', 'quantity' => 10, 'price' => 19.5, 'cost' => 7.50],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-06-26', 'type' => 'buy', 'quantity' => 10, 'price' => 102.5, 'cost' => 0],
                ],
                'expected_positions' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-26', 'quantity' => 10, 'amount' => 902.2, 'contributed_amount' => 909.7, 'average_price' => 90.97],
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-06-26', 'quantity' => 10, 'amount' => 185.1, 'contributed_amount' => 202.5, 'average_price' => 20.25],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-06-26', 'quantity' => 10, 'amount' => 1032.5, 'contributed_amount' => 1025, 'average_price' => 102.5],
                ],
            ],
            'Consolidate on monday - should create positions until friday' => [
                'now' => '2020-06-29 15:00:00',
                'stock_positions' => [],
                'orders' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-25', 'type' => 'buy', 'quantity' => 10, 'price' => 90.22, 'cost' => 7.50],
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-06-25', 'type' => 'buy', 'quantity' => 10, 'price' => 19.5, 'cost' => 7.50],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-06-25', 'type' => 'buy', 'quantity' => 10, 'price' => 102.5, 'cost' => 0],
                ],
                'expected_positions' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-25', 'quantity' => 10, 'amount' => 923.9, 'contributed_amount' => 909.7, 'average_price' => 90.97],
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-06-25', 'quantity' => 10, 'amount' => 189.8, 'contributed_amount' => 202.5, 'average_price' => 20.25],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-06-25', 'quantity' => 10, 'amount' => 1036.8, 'contributed_amount' => 1025, 'average_price' => 102.5],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-26', 'quantity' => 10, 'amount' => 902.2, 'contributed_amount' => 909.7, 'average_price' => 90.97],
                    ['stock_symbol' => 'SQIA3', 'date' => '2020-06-26', 'quantity' => 10, 'amount' => 185.1, 'contributed_amount' => 202.5, 'average_price' => 20.25],
                    ['stock_symbol' => 'XPML11', 'date' => '2020-06-26', 'quantity' => 10, 'amount' => 1032.5, 'contributed_amount' => 1025, 'average_price' => 102.5],
                ],
            ],
            'After market close and new stock in portfolio - should create from order date until now market date' => [
                'now' => '2020-07-03 18:00:00',
                'stock_positions' => [],
                'orders' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-26', 'type' => 'buy', 'quantity' => 10, 'price' => 90.22, 'cost' => 7.50],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-26', 'type' => 'buy', 'quantity' => 5, 'price' => 90.22, 'cost' => 7.50],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-07-01', 'type' => 'buy', 'quantity' => 8, 'price' => 90.22, 'cost' => 7.50],
                ],
                'expected_positions' => [
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-26', 'quantity' => 15, 'amount' => 1353.3, 'contributed_amount' => 1368.3, 'average_price' => 91.22],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-29', 'quantity' => 15, 'amount' => 1384.5, 'contributed_amount' => 1368.3, 'average_price' => 91.22],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-06-30', 'quantity' => 15, 'amount' => 1374.3, 'contributed_amount' => 1368.3, 'average_price' => 91.22],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-07-01', 'quantity' => 23, 'amount' => 2131.64, 'contributed_amount' => 2097.56, 'average_price' => 91.20],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-07-02', 'quantity' => 23, 'amount' => 2127.5, 'contributed_amount' => 2097.56, 'average_price' => 91.20],
                    ['stock_symbol' => 'BOVA11', 'date' => '2020-07-03', 'quantity' => 23, 'amount' => 2143.37, 'contributed_amount' => 2097.56, 'average_price' => 91.20],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestConsolidate
     */

    public function testConsolidate(string $now, array $stock_positions, array $orders, array $expected_positions): void {
        $this->setTestNowForB3DateTime($now);
        $this->saveStockPositions($stock_positions);
        $this->saveOrders($orders);
        $this->translateStockSymbolsToIdsForStockPositions($expected_positions);
        $this->fillUserId($expected_positions);

        StockConsolidator::consolidate();

        $this->assertStockPositions(array_reverse($expected_positions));
    }

    private function setTestNowForB3DateTime(string $date_time): void {
        $now_int_utc = Carbon::parse($date_time, Calendar::B3_TIMEZONE)->utc();
        Carbon::setTestNow($now_int_utc);
    }

    private function translateStockSymbolsToIdsForDates(array &$expected_dates): void {
        foreach ($expected_dates as $symbol => $expected_date) {
            $stock = Stock::getStockBySymbol($symbol);
            unset($expected_dates[$symbol]);
            $expected_dates[$stock->id] = $expected_date;
        }
    }

    private function translateStockSymbolsToIdsForStockPositions(array &$expected_positions): void {
        foreach ($expected_positions as &$position) {
            $stock = Stock::getStockBySymbol($position['stock_symbol']);
            unset($position['symbol']);
            $position['stock_id'] = $stock->id;
        }
    }

    private function fillUserId(array &$expected_positions): void {
        foreach ($expected_positions as &$position) {
            $position['user_id'] = $this->user->id;
        }
    }

    private function assertStockPositions(array $expected_stock_positions): void {
        $created_stock_positions = StockPosition::getBaseQuery()
            ->whereIn('stock_id', array_map(function ($position) {
                return $position['stock_id'];
            }, $expected_stock_positions))
            ->orderBy('date')->get();

        $this->assertCount(sizeof($expected_stock_positions), $created_stock_positions);
        /** @var StockPosition $expected_stock_position */
        foreach ($expected_stock_positions as $expected_stock_position) {
            /** @var StockPosition $created_stock_position */
            $created_stock_position = $created_stock_positions->pop();

            $this->assertEquals($expected_stock_position['user_id'], $created_stock_position->user_id);
            $this->assertEquals($expected_stock_position['stock_id'], $created_stock_position->stock_id);
            $this->assertEquals($expected_stock_position['date'], $created_stock_position->date);
            $this->assertEquals($expected_stock_position['quantity'], $created_stock_position->quantity);
            $this->assertEquals($expected_stock_position['amount'], $created_stock_position->amount);
            $this->assertEquals($expected_stock_position['contributed_amount'], $created_stock_position->contributed_amount);
            $this->assertEquals($expected_stock_position['average_price'], $created_stock_position->average_price);
        }
    }
}
