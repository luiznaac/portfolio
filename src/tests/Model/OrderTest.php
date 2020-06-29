<?php

namespace Tests\Model;

use App\Model\Order\Order;
use App\Model\Stock\Stock;
use Carbon\Carbon;
use Tests\TestCase;

class OrderTest extends TestCase {

    public function testStoreSellOrder_ShouldCalculateAveragePriceAndStore(): void {
        $stock = $this->createStock();
        $date = Carbon::createFromFormat('Y-m-d', '2020-06-26');

        $order = new Order();
        $order->store(
            $stock,
            $date,
            $type = 'sell',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $this->assertEquals(89.47, $order->average_price);
    }

    public function testStoreBuyOrder_ShouldCalculateAveragePriceAndStore(): void {
        $stock = $this->createStock();
        $date = Carbon::createFromFormat('Y-m-d', '2020-06-26');

        $order = new Order();
        $order->store(
            $stock,
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $this->assertEquals(90.97, $order->average_price);
    }

    public function testStoreAndDeleteOrder_ShouldCorrectCalculateSequence(): void {
        $stock = $this->createStock();
        $date = Carbon::createFromFormat('Y-m-d', '2020-06-26');

        $order_1 = new Order();
        $order_1->store(
            $stock,
            $date,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $order_2 = new Order();
        $order_2->store(
            $stock,
            $date,
            $type = 'sell',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $order_3 = new Order();
        $order_3->store(
            $stock,
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

    private function createStock(): Stock {
        $stock = new Stock();
        $stock->symbol = 'BOVA11';
        $stock->save();

        return $stock;
    }
}
