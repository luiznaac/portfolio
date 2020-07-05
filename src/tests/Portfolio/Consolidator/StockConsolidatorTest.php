<?php

namespace Tests\Portfolio\Consolidator;

use App\Model\Order\Order;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\StockConsolidator;
use Carbon\Carbon;
use Tests\TestCase;

class StockConsolidatorTest extends TestCase {

    public function testConsolidateFromBegin(): void {
        $stock = $this->createStock();
        $date_1 = Carbon::parse('2020-06-26');

        $order_1 = new Order();
        $order_1->store(
            $stock,
            $date_1,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $order_2 = new Order();
        $order_2->store(
            $stock,
            $date_1,
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $date_2 = clone $date_1;

        $order_3 = new Order();
        $order_3->store(
            $stock,
            $date_2->addDays(5),
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        StockConsolidator::consolidateFromBegin($stock, $date_2->addDay());

        $stock_position_1 = $this->createStockPosition(
            $stock,
            $date_1,
            $order_1->quantity + $order_2->quantity,
            $order_1->quantity * $order_1->price + $order_2->quantity * $order_2->price,
            ($order_1->quantity * $order_1->price + $order_2->quantity * $order_2->price)/($order_1->quantity + $order_2->quantity)
        );
        $stock_positions[] = $stock_position_1;

        $stock_position_2 = clone $stock_position_1;
        $stock_position_2->date = $date_1->addDays(3)->toDateString();
        $stock_positions[] = $stock_position_2;

        $stock_position_3 = clone $stock_position_1;
        $stock_position_3->date = $date_1->addDay()->toDateString();
        $stock_positions[] = $stock_position_3;

        $stock_position_4 = $this->createStockPosition(
            $stock,
            $date_2->subDay(),
            $stock_positions[0]->quantity + $order_3->quantity,
            $stock_positions[0]->amount + $order_3->quantity * $order_3->price,
            ($stock_positions[0]->amount + $order_3->quantity * $order_3->price)/($stock_positions[0]->quantity + $order_3->quantity)
        );
        $stock_positions[] = $stock_position_4;

        $stock_position_5 = clone $stock_position_4;
        $stock_position_5->date = $date_2->addDay()->toDateString();
        $stock_positions[] = $stock_position_5;

        $this->assertStockPositions($stock_positions);
    }

    private function createStock(string $symbol = 'BOVA11'): Stock {
        $stock = new Stock();
        $stock->symbol = $symbol;
        $stock->save();

        return $stock;
    }

    private function createStockPosition(Stock $stock, Carbon $date, int $quantity, float $amount, float $average_price): StockPosition {
        $position = new StockPosition();
        $position->stock_id = $stock->id;
        $position->date = $date->toDateString();
        $position->quantity = $quantity;
        $position->amount = $amount;
        $position->average_price = $average_price;

        return $position;
    }

    private function assertStockPositions(array $expected_stock_positions): void {
        $created_stock_positions = StockPosition::query()->orderBy('date')->get();

        /** @var StockPosition $expected_stock_position */
        foreach (array_reverse($expected_stock_positions) as $expected_stock_position) {
            /** @var StockPosition $created_stock_position */
            $created_stock_position = $created_stock_positions->pop();

            $this->assertEquals($expected_stock_position->stock_id, $created_stock_position->stock_id);
            $this->assertEquals($expected_stock_position->date, $created_stock_position->date);
            $this->assertEquals($expected_stock_position->quantity, $created_stock_position->quantity);
            $this->assertEquals($expected_stock_position->amount, $created_stock_position->amount);
        }
    }
}
