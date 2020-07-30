<?php

namespace Tests\Portfolio\Utils;

use App\Model\Order\Order;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\ConsolidatorStateMachine;
use App\Portfolio\Utils\Calendar;
use App\Portfolio\Utils\PagesHelper;
use Carbon\Carbon;
use Tests\TestCase;

class PagesHelperTest extends TestCase {

    private $user;

    protected function setUp(): void {
        parent::setUp();
        $this->user = $this->loginWithFakeUser();
    }

    public function dataProviderForTestUpdateAndGetConsolidationState(): array {
        return [
            'No orders and no stock positions' => [
                'today' => '2020-07-10',
                'order' => [],
                'stock_positions' => [],
                'expected_result' => 0,
            ],
            'With order and no stock positions' => [
                'today' => '2020-07-10',
                'order' => ['updated_at' => '2020-07-10'],
                'stock_positions' => [],
                'expected_result' => 1,
            ],
            'Order after stock position' => [
                'today' => '2020-07-09',
                'order' => ['updated_at' => '2020-07-11'],
                'stock_positions' => [['updated_at' => '2020-07-10']],
                'expected_result' => 1,
            ],
            'Stock position date before last working day post market close' => [
                'today' => '2020-07-10 18:30:00',
                'order' => ['updated_at' => '2020-07-09'],
                'stock_positions' => [
                    [
                        'date' => '2020-07-08',
                        'updated_at' => '2020-07-09'
                    ],
                ],
                'expected_result' => 1,
            ],
            'Stock position date on last working day pre market close' => [
                'today' => '2020-07-10 15:30:00',
                'order' => ['date' => '2020-07-09', 'updated_at' => '2020-07-09'],
                'stock_positions' => [
                    [
                        'date' => '2020-07-09',
                        'updated_at' => '2020-07-10'
                    ],
                ],
                'expected_result' => 0,
            ],
            'Most recent stock position updated at is not the last position and has order after last stock position updated' => [
                'today' => '2020-07-11 15:30:00',
                'order' => ['updated_at' => '2020-07-10 20:21:55'],
                'stock_positions' => [
                    [
                        'date' => '2020-07-09',
                        'updated_at' => '2020-07-10 20:21:21'
                    ],
                    [
                        'date' => '2020-07-10',
                        'updated_at' => '2020-07-10 15:00:58'
                    ],
                ],
                'expected_result' => 1,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestUpdateAndGetConsolidationState
     */
    public function testUpdateAndGetConsolidationState(string $today, array $order, array $stock_positions, int $expected_result): void {
        $today_in_utc = Carbon::parse($today, Calendar::B3_TIMEZONE)->utc();
        Carbon::setTestNow($today_in_utc);
        $this->createOrder($order);
        $this->createStockPositions($stock_positions);

        $this->assertEquals($expected_result, PagesHelper::updateAndGetConsolidationState());
    }

    private function createOrder(array $order): void {
        if(empty($order)) {
            return;
        }

        Order::createOrder(
            'SQIA3',
            isset($order['date']) ? Carbon::parse($order['date']) : Carbon::now()->subDays(2),
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );
    }

    private function createStockPositions(array $stock_positions): void {
        if(empty($stock_positions)) {
            return;
        }

        foreach ($stock_positions as $stock_position) {
            $position = new StockPosition();
            $position->user_id = $this->user->id;
            $position->stock_id = Stock::getStockBySymbol('SQIA3')->id;
            $position->date = isset($stock_position['date']) ? Carbon::parse($stock_position['date']) : Carbon::today();
            $position->quantity = 1;
            $position->amount = 1;
            $position->contributed_amount = 1;
            $position->average_price = 1;
            $position->updated_at = $stock_position['updated_at'];

            $position->save();
        }
    }
}
