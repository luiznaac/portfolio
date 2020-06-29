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

    private function createStock(): Stock {
        $stock = new Stock();
        $stock->symbol = 'BOVA11';
        $stock->save();

        return $stock;
    }
}
