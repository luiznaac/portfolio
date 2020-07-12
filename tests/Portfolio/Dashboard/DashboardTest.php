<?php

namespace Tests\Model\Stock\Position;

use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Portfolio\Dashboard\Dashboard;
use Carbon\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase {

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

    public function testGetStockData(): void {
        $stock_positions = $this->prepareScenario();
        $stock_positions_by_type = $this->generateExpectedDataForStockPositions($stock_positions);
        [$amount_updated, $amount_contributed, $overall_variation] = $this->generateExpectedOverallData($stock_positions);

        $data = Dashboard::getData();

        $this->assertEquals($stock_positions_by_type, $data['stock_positions_by_type']);
        $this->assertEquals($amount_updated, $data['amount_updated']);
        $this->assertEquals($amount_contributed, $data['amount_contributed']);
        $this->assertEquals($overall_variation, $data['overall_variation']);
    }

    private function generateExpectedDataForStockPositions(array $stock_positions): array {
        $stock_positions_by_type = [];
        /** @var StockPosition $stock_position */
        foreach ($stock_positions as $stock_position) {
            $stock = Stock::find($stock_position->stock_id);
            $stock_positions_by_type[$stock->stock_type_id]['positions'][] = [
                'position' => $stock_position,
                'percentage' => 33.33,
                'gross_result' => 5.0,
                'gross_result_percentage' => 50.0,
            ];
            $stock_positions_by_type[$stock->stock_type_id]['percentage'] = 33.33;
        }

        return $stock_positions_by_type;
    }

    private function generateExpectedOverallData(array $stock_positions): array {
        $amount_updated = 0.0;
        $amount_contributed = 0.0;
        /** @var StockPosition $stock_position */
        foreach ($stock_positions as $stock_position) {
            $amount_updated += $stock_position->amount;
            $amount_contributed += $stock_position->contributed_amount;
        }

        $overall_variation = round((($amount_updated - $amount_contributed)/$amount_contributed)*100, 2);

        return [$amount_updated, $amount_contributed, $overall_variation];
    }

    private function prepareScenario(): array {
        $stock_1 = Stock::getStockBySymbol('BOVA11');
        $stock_2 = Stock::getStockBySymbol('SQIA3');
        $stock_3 = Stock::getStockBySymbol('XPML11');

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

        $this->createStockPosition(
            $stock_3,
            $date_1
        );

        $stock_3_position_2 = $this->createStockPosition(
            $stock_3,
            $date_2
        );

        return [
            StockPosition::find($stock_1_position_2->id),
            StockPosition::find($stock_2_position_2->id),
            StockPosition::find($stock_3_position_2->id)
        ];
    }

    private function createStockPosition(Stock $stock, Carbon $date): StockPosition {
        $position = new StockPosition();
        $position->user_id = $this->user->id;
        $position->stock_id = $stock->id;
        $position->date = $date->toDateString();
        $position->quantity = 10;
        $position->amount = 15;
        $position->contributed_amount = 10;
        $position->average_price = 1;
        $position->save();

        return $position;
    }
}
