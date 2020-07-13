<?php

namespace Tests\Portfolio\Utils;

use App\Model\Order\Order;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
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

    public function dataProviderForTestShouldUpdatePositions(): array {
        return [
            'No orders and no stock positions' => [
                'today' => '2020-07-10',
                'order' => [],
                'stock_position' => [],
                'expected_result' => false,
            ],
            'With order and no stock positions' => [
                'today' => '2020-07-10',
                'order' => ['updated_at' => '2020-07-10'],
                'stock_position' => [],
                'expected_result' => true,
            ],
            'No order and with stock positions' => [
                'today' => '2020-07-10',
                'order' => [],
                'stock_position' => ['updated_at' => '2020-07-10'],
                'expected_result' => true,
            ],
            'Order after stock position' => [
                'today' => '2020-07-09',
                'order' => ['updated_at' => '2020-07-11'],
                'stock_position' => ['updated_at' => '2020-07-10'],
                'expected_result' => true,
            ],
            'Order before stock position' => [
                'today' => '2020-07-09',
                'order' => ['updated_at' => '2020-07-10'],
                'stock_position' => ['updated_at' => '2020-07-11'],
                'expected_result' => false,
            ],
            'Stock position date before last working day post market close' => [
                'today' => '2020-07-10 18:30:00',
                'order' => ['updated_at' => '2020-07-09'],
                'stock_position' => [
                    'date' => '2020-07-08',
                    'updated_at' => '2020-07-09'
                ],
                'expected_result' => true,
            ],
            'Stock position date on last working day pre market close' => [
                'today' => '2020-07-10 15:30:00',
                'order' => ['updated_at' => '2020-07-09'],
                'stock_position' => [
                    'date' => '2020-07-09',
                    'updated_at' => '2020-07-10'
                ],
                'expected_result' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestShouldUpdatePositions
     */
    public function testShouldUpdatePositions(string $today, array $order, array $stock_position, bool $expected_result): void {
        $today_in_utc = Carbon::parse($today, Calendar::B3_TIMEZONE)->utc();
        Carbon::setTestNow($today_in_utc);
        $this->createOrder($order);
        $this->createStockPosition($stock_position);

        $this->assertEquals($expected_result, PagesHelper::shouldUpdatePositions());
    }

    private function createOrder(array $order): void {
        if(empty($order)) {
            return;
        }

        $order_1 = new Order();
        $order_1->updated_at = $order['updated_at'];
        $order_1->store(
            Stock::getStockBySymbol('SQIA3'),
            Carbon::now(),
            $type = 'buy',
            $quantity = 10,
            $price = 90.22,
            $cost = 7.50
        );
    }

    private function createStockPosition(array $stock_position): void {
        if(empty($stock_position)) {
            return;
        }

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
