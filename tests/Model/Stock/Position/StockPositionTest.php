<?php

namespace Tests\Model\Stock\Position;

use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use Carbon\Carbon;
use Tests\TestCase;

class StockPositionTest extends TestCase {

    private $user;

    protected function setUp(): void {
        parent::setUp();
        $this->user = $this->loginWithFakeUser();

        $this->createStockPosition(
            Stock::getStockBySymbol('BOVA11'),
            Carbon::parse('2020-06-30')
        );

        $this->createStockPosition(
            Stock::getStockBySymbol('SQIA3'),
            Carbon::parse('2020-07-01')
        );

        $this->user = $this->loginWithFakeUser();
    }

    public function testGetPositionsForStock(): void {
        $stock_1 = Stock::getStockBySymbol('BOVA11');

        $date_1 = Carbon::parse('2020-06-26');
        $date_2 = Carbon::parse('2020-06-29');

        $expected_stock_positions[] = $this->createStockPosition(
            $stock_1,
            $date_1
        );

        $expected_stock_positions[] =  $this->createStockPosition(
            $stock_1,
            $date_2
        );

        $stock_positions = StockPosition::getPositionsForStock($stock_1);

        $actual_stock_positions = [];
        foreach ($stock_positions as $stock_position) {
            $actual_stock_positions[] = $stock_position;
        }

        $this->assertCount(2, $stock_positions);
        $this->assertStockPositionsForGetPositionsForStock($expected_stock_positions, array_reverse($actual_stock_positions));
    }

    public function testLastStockPositions(): void {
        $stock_1 = Stock::getStockBySymbol('BOVA11');
        $stock_2 = Stock::getStockBySymbol('SQIA3');

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

        $this->assertStockPositionsForGetLastStockPositions($expected_stock_positions, $actual_stock_positions);
    }

    private function createStockPosition(Stock $stock, Carbon $date): StockPosition {
        $position = new StockPosition();
        $position->user_id = $this->user->id;
        $position->stock_id = $stock->id;
        $position->date = $date->toDateString();
        $position->quantity = 10;
        $position->amount = 10;
        $position->contributed_amount = 10;
        $position->average_price = 1;
        $position->save();

        return $position;
    }

    private function assertStockPositionsForGetPositionsForStock(array $expected_stock_positions, array $actual_stock_positions): void {
        foreach ($actual_stock_positions as $actual_stock_position) {
            $expected_stock_position = array_shift($expected_stock_positions);
            $this->assertStockPosition($expected_stock_position, $actual_stock_position);
        }
    }

    private function assertStockPositionsForGetLastStockPositions(array $expected_stock_positions, array $actual_stock_positions): void {
        foreach ($actual_stock_positions as $actual_stock_position) {
            $expected_stock_position = $expected_stock_positions[$actual_stock_position->stock_id];
            $this->assertStockPosition($expected_stock_position, $actual_stock_position);
        }
    }

    private function assertStockPosition(StockPosition $expected_stock_position, StockPosition $actual_stock_position): void {
        $this->assertEquals($expected_stock_position->id, $actual_stock_position->id);
        $this->assertEquals($expected_stock_position->user_id, $actual_stock_position->user_id);
        $this->assertEquals($expected_stock_position->stock_id, $actual_stock_position->stock_id);
        $this->assertEquals($expected_stock_position->date, $actual_stock_position->date);
    }
}
