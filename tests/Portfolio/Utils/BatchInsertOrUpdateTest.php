<?php

namespace Tests\Portfolio\Utils;

use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Model\Stock\StockPrice;
use App\Portfolio\Utils\BatchInsertOrUpdate;
use Tests\TestCase;

class BatchInsertOrUpdateTest extends TestCase {

    private $user;

    protected function setUp(): void {
        parent::setUp();
        $this->user = $this->loginWithFakeUser();
    }

    public function testBatchInsertOrUpdatedWithStockPositions(): void {
        $stock_1 = new Stock();
        $stock_1->symbol = 'IVVB11';
        $stock_1->save();

        $stock_2 = new Stock();
        $stock_2->symbol = 'ITSA4';
        $stock_2->save();

        $data = [
            [
                'stock_id' => $stock_1->id,
                'user_id' => $this->user->id,
                'date' => '2020-07-16',
                'quantity' => 123,
                'amount' => 12.5,
                'contributed_amount' => 12.5,
                'average_price' => 12.5,
            ],
            [
                'stock_id' => $stock_2->id,
                'user_id' => $this->user->id,
                'date' => '2020-03-16',
                'quantity' => 123,
                'amount' => 12.5,
                'contributed_amount' => 12.5,
                'average_price' => 12.5,
            ],
            [
                'stock_id' => $stock_1->id,
                'user_id' => $this->user->id,
                'date' => '2020-07-16',
                'quantity' => 11,
                'amount' => 1.5,
                'contributed_amount' => 3.5,
                'average_price' => 7.5,
            ],
        ];

        BatchInsertOrUpdate::execute('stock_positions', $data);

        $positions = StockPosition::getBaseQuery()
            ->whereIn('stock_id', [$stock_1->id, $stock_2->id])
            ->whereIn('date', ['2020-07-16', '2020-03-16'])
            ->get()->toArray();

        $this->assertCount(2, $positions);
        $this->assertEquals(11, $positions[0]['quantity']);
        $this->assertEquals(1.5, $positions[0]['amount']);
        $this->assertEquals(3.5, $positions[0]['contributed_amount']);
        $this->assertEquals(7.5, $positions[0]['average_price']);
        $this->assertEquals(123, $positions[1]['quantity']);
        $this->assertEquals(12.5, $positions[1]['amount']);
        $this->assertEquals(12.5, $positions[1]['contributed_amount']);
        $this->assertEquals(12.5, $positions[1]['average_price']);
    }

    public function testBatchInsertOrUpdatedWithStockPrices(): void {
        $stock_1 = new Stock();
        $stock_1->symbol = 'IVVB11';
        $stock_1->save();

        $stock_2 = new Stock();
        $stock_2->symbol = 'ITSA4';
        $stock_2->save();

        $data = [
            [
                'stock_id' => $stock_1->id,
                'date' => '2020-07-16',
                'price' => 123.45,
            ],
            [
                'stock_id' => $stock_2->id,
                'date' => '2020-05-16',
                'price' => 456.99,
            ],
            [
                'stock_id' => $stock_2->id,
                'date' => '2020-05-16',
                'price' => 555555.99,
            ],
        ];

        BatchInsertOrUpdate::execute('stock_prices', $data);

        $prices = StockPrice::query()
            ->whereIn('stock_id', [$stock_1->id, $stock_2->id])
            ->whereIn('date', ['2020-07-16', '2020-05-16'])
            ->get()->toArray();

        $this->assertCount(2, $prices);
        $this->assertEquals(123.45, $prices[0]['price']);
        $this->assertEquals(555555.99, $prices[1]['price']);
    }
}
