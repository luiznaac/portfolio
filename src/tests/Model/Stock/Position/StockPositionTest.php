<?php

namespace Tests\Model\Stock\Position;

use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use Carbon\Carbon;
use Tests\TestCase;

class StockPositionTest extends TestCase {

    public function testLastStockPositions(): void {
        $stock_1 = new Stock();
        $stock_1->store('BOVA11');
        $stock_2 = new Stock();
        $stock_2->store('SQIA3');

        $date_1 = Carbon::parse('2020-06-26');
        $date_2 = Carbon::parse('2020-06-29');

        $this->createStockPosition(
            $stock_1,
            $date_1
        );

        $stock_1_position_2 = $this->createStockPosition(
            $stock_1,
            $date_2
        );

        $this->createStockPosition(
            $stock_2,
            $date_1
        );

        $stock_2_position_2 = $this->createStockPosition(
            $stock_2,
            $date_2
        );

        $expected_stock_positions = [
            $stock_1->id => $stock_1_position_2,
            $stock_2->id => $stock_2_position_2
        ];
        $actual_stock_positions = StockPosition::getLastStockPositions();

        $this->assertStockPositions($expected_stock_positions, $actual_stock_positions);
    }

    private function createStockPosition(Stock $stock, Carbon $date): StockPosition {
        $position = new StockPosition();
        $position->stock_id = $stock->id;
        $position->date = $date->toDateString();
        $position->quantity = 10;
        $position->amount = 10;
        $position->contributed_amount = 10;
        $position->average_price = 1;
        $position->save();

        return $position;
    }

    private function assertStockPositions(array $expected_stock_positions, array $actual_stock_positions): void {
        foreach ($actual_stock_positions as $actual_stock_position) {
            $expected_stock_position = $expected_stock_positions[$actual_stock_position->stock_id];
            $this->assertEquals($expected_stock_position->id, $actual_stock_position->id);
            $this->assertEquals($expected_stock_position->stock_id, $actual_stock_position->stock_id);
            $this->assertEquals($expected_stock_position->date, $actual_stock_position->date);
        }
    }
}
