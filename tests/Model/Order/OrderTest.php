<?php

namespace Tests\Model\Order;

use App\Model\Order\Order;
use App\Model\Stock\Stock;
use Carbon\Carbon;
use Tests\TestCase;

class OrderTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        $this->loginWithFakeUser();
        $date = Carbon::parse('2020-06-26');

        Order::createOrder(
            'FLRY3',
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        Order::createOrder(
            'XPML11',
            $date,
            $type = 'sell',
            $quantity = 5,
            $price = 90.22,
            $cost = 7.50
        );

        Order::createOrder(
            'BOVA11',
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        Order::createOrder(
            'MXRF11',
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $this->loginWithFakeUser();
    }

    public function testDelete_ShouldTouchNextOrderFromDeletedOrder(): void {
        Carbon::setTestNow('2020-07-15 15:15:15');
        $orders = [
            ['stock_symbol' => 'BOVA11', 'date' => '2020-06-29', 'type' => 'buy', 'quantity' => 10, 'price' => 90.22, 'cost' => 7.50],
            ['stock_symbol' => 'BOVA11', 'date' => '2020-06-30', 'type' => 'buy', 'quantity' => 5, 'price' => 90.22, 'cost' => 7.50],
        ];

        $this->saveOrders($orders);
        $stock = Stock::getStockBySymbol('BOVA11');

        $order_1 = Order::getBaseQuery()->where('stock_id', $stock->id)->where('date', '2020-06-29')->get()->first();

        Carbon::setTestNow('2020-07-18 18:18:18');
        $order_1->delete();

        $order_2 = Order::getBaseQuery()->where('stock_id', $stock->id)->where('date', '2020-06-30')->get()->first();

        $this->assertEquals('2020-07-18 18:18:18', $order_2->updated_at);
    }

    public function testDelete_ShouldTouchPreviousOrderFromDeletedOrder(): void {
        Carbon::setTestNow('2020-07-15 15:15:15');
        $orders = [
            ['stock_symbol' => 'BOVA11', 'date' => '2020-06-29', 'type' => 'buy', 'quantity' => 10, 'price' => 90.22, 'cost' => 7.50],
            ['stock_symbol' => 'BOVA11', 'date' => '2020-06-30', 'type' => 'buy', 'quantity' => 5, 'price' => 90.22, 'cost' => 7.50],
            ['stock_symbol' => 'BOVA11', 'date' => '2020-07-01', 'type' => 'buy', 'quantity' => 5, 'price' => 90.22, 'cost' => 7.50],
        ];

        $this->saveOrders($orders);
        $stock = Stock::getStockBySymbol('BOVA11');

        $order_1 = Order::getBaseQuery()->where('stock_id', $stock->id)->where('date', '2020-06-30')->get()->first();

        Carbon::setTestNow('2020-07-18 18:18:18');
        $order_1->delete();

        $order_2 = Order::getBaseQuery()->where('stock_id', $stock->id)->where('date', '2020-06-29')->get()->first();
        $order_3 = Order::getBaseQuery()->where('stock_id', $stock->id)->where('date', '2020-07-01')->get()->first();

        $this->assertEquals('2020-07-18 18:18:18', $order_2->updated_at);
        $this->assertEquals('2020-07-15 15:15:15', $order_3->updated_at);
    }

    public function testGetAllOrdersForStockUntilDate(): void {
        $date = Carbon::parse('2020-06-22');
        $stock = Stock::getStockBySymbol('BOVA11');

        $expected_orders[] = Order::createOrder(
            $stock->symbol,
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $expected_orders[] = Order::createOrder(
            $stock->symbol,
            $date->addDay(),
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $expected_orders[] = Order::createOrder(
            $stock->symbol,
            $date->addDay(),
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $actual_orders = Order::getAllOrdersForStockUntilDate($stock, $date->subDay())->toArray();

        $this->assertCount(2, $actual_orders);
        $this->assertEquals($expected_orders[0]->toArray(), $actual_orders[0]);
        $this->assertEquals($expected_orders[1]->toArray(), $actual_orders[1]);

        $actual_orders = Order::getAllOrdersForStockUntilDate($stock, $date->addDay())->toArray();

        $this->assertCount(3, $actual_orders);
        $this->assertEquals($expected_orders[0]->toArray(), $actual_orders[0]);
        $this->assertEquals($expected_orders[1]->toArray(), $actual_orders[1]);
        $this->assertEquals($expected_orders[2]->toArray(), $actual_orders[2]);
    }

    public function testGetAllStocksWithOrdersWhenThereIsNoOrders_ShouldReturnEmptyArray(): void {
        $stocks = Order::getAllStocksWithOrders();

        $this->assertEmpty($stocks);
    }

    public function testGetAllStocksWithOrders_ShouldReturnStocks(): void {
        $date_1 = Carbon::parse('2020-06-03');

        Order::createOrder(
            'BOVA11',
            $date_1,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        Order::createOrder(
            'MXRF11',
            $date_1->addDay(),
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $deleted_order = Order::createOrder(
            'FLRY3',
            $date_1->addDay(),
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );
        $deleted_order->delete();

        $expected_stocks[] = Stock::getStockBySymbol('BOVA11');
        $expected_stocks[] = Stock::getStockBySymbol('MXRF11');

        $actual_stocks = Order::getAllStocksWithOrders();

        $this->assertEquals($expected_stocks, $actual_stocks);
    }

    public function testGetDateOfFirstContributionWithoutOrders_ShouldReturnNull(): void {
        $date = Order::getDateOfFirstContribution();

        $this->assertNull($date);
    }

    public function testGetDateOfFirstContributionWithStockAndWithoutStock(): void {
        $date_0 = Carbon::parse('2020-06-03');

        Order::createOrder(
            'BOVA11',
            $date_0,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $date_1 = Carbon::parse('2020-06-10');

        Order::createOrder(
            'MXRF11',
            $date_1,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $date_2 = Carbon::parse('2020-06-26');

        Order::createOrder(
            'MXRF11',
            $date_2,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        Order::createOrder(
            'BOVA11',
            $date_2,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $stock = Stock::getStockBySymbol('MXRF11');
        $first_date = Order::getDateOfFirstContribution($stock);

        $this->assertEquals($date_1, $first_date);

        $first_date = Order::getDateOfFirstContribution();

        $this->assertEquals($date_0, $first_date);
    }

    public function testCreateOrderWithNewStock_ShouldCreateStockAndOrder(): void {
        $date = Carbon::createFromFormat('Y-m-d', '2020-06-26');

        Order::createOrder(
            'MXRF11',
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $stock = Stock::getStockBySymbol('MXRF11');

        $this->assertNotNull($stock);
    }

    public function testStoreSellOrder_ShouldCalculateAveragePriceAndStore(): void {
        $date = Carbon::createFromFormat('Y-m-d', '2020-06-26');

        $order = Order::createOrder(
            'BOVA11',
            $date,
            $type = 'sell',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $this->assertEquals(89.47, $order->average_price);
    }

    public function testStoreBuyOrder_ShouldCalculateAveragePriceAndStore(): void {
        $date = Carbon::createFromFormat('Y-m-d', '2020-06-26');

        $order = Order::createOrder(
            'BOVA11',
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $this->assertEquals(90.97, $order->average_price);
    }

    public function testGetStockSymbol(): void {
        $date = Carbon::createFromFormat('Y-m-d', '2020-06-26');

        $order_1 = Order::createOrder(
            'BOVA11',
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $this->assertEquals('BOVA11', $order_1->getStockSymbol());
    }

    public function testGetTotal(): void {
        $date = Carbon::createFromFormat('Y-m-d', '2020-06-26');

        $order_1 = Order::createOrder(
            'MXRF11',
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $order_2 = Order::createOrder(
            'MXRF11',
            $date,
            $type = 'sell',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $this->assertEquals(909.7, $order_1->getTotal());
        $this->assertEquals(894.7, $order_2->getTotal());
    }

    public function testConsolidateQuantityForStock(): void {
        $stock_symbol_1 = 'FLRY3';
        $stock_symbol_2 = 'XPML11';
        $date = Carbon::createFromFormat('Y-m-d', '2020-06-26');

        Order::createOrder(
            $stock_symbol_1,
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        Order::createOrder(
            $stock_symbol_1,
            $date,
            $type = 'sell',
            $quantity = 5,
            $price = 90.22,
            $cost = 7.50
        );

        $stock_1 = Stock::getStockBySymbol($stock_symbol_1);

        $this->assertEquals(5, Order::consolidateQuantityForStock($stock_1->id));

        Order::createOrder(
            $stock_symbol_1,
            $date,
            $type = 'buy',
            $quantity = 80,
            $price = 90.22,
            $cost = 7.50
        );

        Order::createOrder(
            $stock_symbol_2,
            $date,
            $type = 'buy',
            $quantity = 50,
            $price = 90.22,
            $cost = 7.50
        );

        $stock_2 = Stock::getStockBySymbol($stock_symbol_2);

        $this->assertEquals(85, Order::consolidateQuantityForStock($stock_1->id));
        $this->assertEquals(50, Order::consolidateQuantityForStock($stock_2->id));
    }
}
