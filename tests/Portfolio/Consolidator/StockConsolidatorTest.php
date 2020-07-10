<?php

namespace Tests\Portfolio\Consolidator;

use App\Model\Order\Order;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\StockConsolidator;
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

    public function testUpdatePositions_ShouldUpdatePosition(): void {
        $stock_1 = Stock::getStockBySymbol('SQIA3');
        Carbon::setTestNow('2020-06-30');

        $order_1 = new Order();
        $order_1->store(
            $stock_1,
            Carbon::now()->subDays(2),
            $type = 'buy',
            $quantity = 10,
            $price = 18.22,
            $cost = 7.50
        );

        $stock_position_1 = $this->createStockPosition(
            $stock_1,
            Carbon::now()->subDay(),
            10,
            18.72 * 10,
            18.22 * 10,
            18.22
        );

        StockConsolidator::updatePositions();

        $order_2 = new Order();
        $order_2->store(
            $stock_1,
            Carbon::now()->subDay(),
            $type = 'buy',
            $quantity = 10,
            $price = 15.22,
            $cost = 7.50
        );

        $stock_position_1->quantity = $stock_position_1->quantity + $order_2->quantity;
        $stock_position_1->amount = 18.72 * $stock_position_1->quantity;
        $stock_position_1->contributed_amount = $stock_position_1->contributed_amount + $order_2->quantity * $order_2->price;
        $stock_position_1->average_price = (18.22 + 15.22)/2;

        StockConsolidator::updatePositions();

        $this->assertStockPositions([$stock_position_1]);
        $this->assertCount(1, StockPosition::all());
    }

    public function testUpdatePositionsOnMonday_ShouldOnlyCreateFridayForAllStocks(): void {
        $stock_1 = Stock::getStockBySymbol('SQIA3');
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

        $stock_position_1 = $this->createStockPosition(
            $stock_1,
            Carbon::now()->subDays(3),
            $order_1->quantity,
            18.51 * $order_1->quantity,
            $order_1->quantity * $order_1->price,
            $order_1->price
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

        $stock_position_2 = $this->createStockPosition(
            $stock_2,
            Carbon::now()->subDays(3),
            $order_2->quantity,
            103.25 * $order_2->quantity,
            $order_2->quantity * $order_2->price,
            $order_2->price
        );

        StockConsolidator::updatePositions();

        $this->assertStockPositions([$stock_position_1, $stock_position_2]);
    }

    public function testUpdatePositionsOnWeekDay_ShouldOnlyCreatePreviousLastWorkingDateForAllStocks(): void {
        $stock_1 = Stock::getStockBySymbol('SQIA3');
        $stock_2 = Stock::getStockBySymbol('XPML11');
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

        $stock_position_1 = $this->createStockPosition(
            $stock_1,
            Carbon::now()->subDay(),
            $order_1->quantity,
            19.5 * $order_1->quantity,
            $order_1->quantity * $order_1->price,
            $order_1->price
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

        $order_3 = new Order();
        $order_3->store(
            $stock_2,
            Carbon::now(),
            $type = 'buy',
            $quantity = 123,
            $price = 333.22,
            $cost = 7.50
        );

        $stock_position_2 = $this->createStockPosition(
            $stock_2,
            Carbon::now()->subDay(),
            $order_2->quantity,
            105 * $order_2->quantity,
            $order_2->quantity * $order_2->price,
            $order_2->price
        );

        StockConsolidator::updatePositions();

        $this->assertStockPositions([$stock_position_1, $stock_position_2]);
    }

    public function testUpdatePositionsOnWeekend_ShouldOnlyCreateLastWorkingDateForAllStocks(): void {
        $stock_1 = Stock::getStockBySymbol('SQIA3');
        $stock_2 = Stock::getStockBySymbol('XPML11');
        Carbon::setTestNow('2020-06-27');

        $order_1 = new Order();
        $order_1->store(
            $stock_1,
            Carbon::now()->subDay(),
            $type = 'buy',
            $quantity = 10,
            $price = 15.22,
            $cost = 7.50
        );

        $stock_position_1 = $this->createStockPosition(
            $stock_1,
            Carbon::now()->subDay(),
            $order_1->quantity,
            18.51 * $order_1->quantity,
            $order_1->quantity * $order_1->price,
            $order_1->price
        );

        $order_2 = new Order();
        $order_2->store(
            $stock_2,
            Carbon::now()->subDay(),
            $type = 'buy',
            $quantity = 10,
            $price = 15.22,
            $cost = 7.50
        );

        $stock_position_2 = $this->createStockPosition(
            $stock_2,
            Carbon::now()->subDay(),
            $order_2->quantity,
            103.25 * $order_2->quantity,
            $order_2->quantity * $order_2->price,
            $order_2->price
        );

        StockConsolidator::updatePositions();

        $this->assertStockPositions([$stock_position_1, $stock_position_2]);
    }

    public function testUpdatePositionForStockOnWeekend_ShouldOnlyCreateLastWorkingDate(): void {
        $stock = Stock::getStockBySymbol('BOVA11');
        Carbon::setTestNow('2020-06-28');

        $order_1 = new Order();
        $order_1->store(
            $stock,
            Carbon::now()->subDays(2),
            $type = 'buy',
            $quantity = 10,
            $price = 15.22,
            $cost = 7.50
        );

        $stock_position_1 = $this->createStockPosition(
            $stock,
            Carbon::now()->subDays(2),
            $order_1->quantity,
            90.22 * $order_1->quantity,
            $order_1->quantity * $order_1->price,
            $order_1->price
        );

        StockConsolidator::updatePositionForStock($stock);

        $this->assertStockPositions([$stock_position_1]);
    }

    public function testConsolidateFromBeginWithoutOrders_ShouldNotCreatePositions(): void {
        $stock = Stock::getStockBySymbol('BOVA11');

        StockConsolidator::consolidateFromBegin($stock);

        $created_stock_positions = StockPosition::query()->orderBy('date')->get();

        $this->assertEmpty($created_stock_positions);
    }

    public function testConsolidateFromBegin(): void {
        $stock = Stock::getStockBySymbol('BOVA11');
        Carbon::setTestNow('2020-06-26');

        $order_1 = new Order();
        $order_1->store(
            $stock,
            Carbon::now(),
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );

        $order_2 = new Order();
        $order_2->store(
            $stock,
            Carbon::now(),
            $type = 'buy',
            $quantity = 5,
            $price = 90.22,
            $cost = 7.50
        );

        $order_3 = new Order();
        $order_3->store(
            $stock,
            Carbon::now()->addDays(5),
            $type = 'buy',
            $quantity = 8,
            $price = 90.22,
            $cost = 7.50
        );

        $stock_position_1 = $this->createStockPosition(
            $stock,
            Carbon::now(),
            $order_1->quantity + $order_2->quantity,
            90.22 * ($order_1->quantity + $order_2->quantity),
            $order_1->quantity * $order_1->price + $order_2->quantity * $order_2->price,
            ($order_1->quantity * $order_1->price + $order_2->quantity * $order_2->price)/($order_1->quantity + $order_2->quantity)
        );
        $stock_positions[] = $stock_position_1;

        $stock_position_2 = clone $stock_position_1;
        $stock_position_2->date = Carbon::now()->addDays(3)->toDateString();
        $stock_position_2->amount = 92.3 * $stock_position_1->quantity;
        $stock_positions[] = $stock_position_2;

        $stock_position_3 = clone $stock_position_1;
        $stock_position_3->date = Carbon::now()->addDays(4)->toDateString();
        $stock_position_3->amount = 91.62 * $stock_position_1->quantity;
        $stock_positions[] = $stock_position_3;

        $stock_position_4 = $this->createStockPosition(
            $stock,
            Carbon::now()->addDays(5),
            $stock_positions[0]->quantity + $order_3->quantity,
            92.68 * ($stock_positions[0]->quantity + $order_3->quantity),
            $stock_positions[0]->contributed_amount + $order_3->quantity * $order_3->price,
            ($stock_positions[0]->contributed_amount + $order_3->quantity * $order_3->price)/($stock_positions[0]->quantity + $order_3->quantity)
        );
        $stock_positions[] = $stock_position_4;

        $stock_position_5 = clone $stock_position_4;
        $stock_position_5->date = Carbon::now()->addDays(6)->toDateString();
        $stock_position_5->amount = 92.5 * $stock_position_4->quantity;
        $stock_positions[] = $stock_position_5;

        Carbon::setTestNow(Carbon::now()->addDays(7));
        StockConsolidator::consolidateFromBegin($stock);

        $this->assertStockPositions($stock_positions);
    }

    private function createStockPosition(Stock $stock, Carbon $date, int $quantity, float $amount, float $contributed_amount, float $average_price): StockPosition {
        $position = new StockPosition();
        $position->user_id = $this->user->id;
        $position->stock_id = $stock->id;
        $position->date = $date->toDateString();
        $position->quantity = $quantity;
        $position->amount = $amount;
        $position->contributed_amount = $contributed_amount;
        $position->average_price = $average_price;

        return $position;
    }

    private function assertStockPositions(array $expected_stock_positions): void {
        $created_stock_positions = StockPosition::query()
            ->whereIn('stock_id', array_map(function ($position) {
                return $position->stock_id;
            }, $expected_stock_positions))
            ->orderBy('date')->get();

        /** @var StockPosition $expected_stock_position */
        foreach (array_reverse($expected_stock_positions) as $expected_stock_position) {
            /** @var StockPosition $created_stock_position */
            $created_stock_position = $created_stock_positions->pop();

            $this->assertEquals($expected_stock_position->user_id, $created_stock_position->user_id);
            $this->assertEquals($expected_stock_position->stock_id, $created_stock_position->stock_id);
            $this->assertEquals($expected_stock_position->date, $created_stock_position->date);
            $this->assertEquals($expected_stock_position->quantity, $created_stock_position->quantity);
            $this->assertEquals($expected_stock_position->amount, $created_stock_position->amount);
            $this->assertEquals($expected_stock_position->contributed_amount, $created_stock_position->contributed_amount);
            $this->assertEquals($expected_stock_position->average_price, $created_stock_position->average_price);
        }
    }
}
