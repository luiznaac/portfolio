<?php

namespace Tests\Model\Order;

use App\Model\Order\Order;
use App\Model\Stock\Stock;
use Carbon\Carbon;
use Tests\TestCase;

class OrderTest extends TestCase {

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

    public function testStoreAndDeleteOrder_ShouldCorrectCalculateSequence(): void {
        $date = Carbon::createFromFormat('Y-m-d', '2020-06-26');

        $order_1 = Order::createOrder(
            'BOVA11',
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $order_2 = Order::createOrder(
            'BOVA11',
            $date,
            $type = 'sell',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $order_3 = Order::createOrder(
            'BOVA11',
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $this->assertEquals(1, $order_1->sequence);
        $this->assertEquals(2, $order_2->sequence);
        $this->assertEquals(3, $order_3->sequence);

        $order_1->delete();
        $order_2->refresh();
        $order_3->refresh();

        $this->assertEquals(1, $order_2->sequence);
        $this->assertEquals(2, $order_3->sequence);
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

        $this->assertEquals(5, Order::consolidateQuantityForStock($stock_1));

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

        $this->assertEquals(85, Order::consolidateQuantityForStock($stock_1));
        $this->assertEquals(50, Order::consolidateQuantityForStock($stock_2));
    }
}
