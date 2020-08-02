<?php

namespace Tests\Model\Stock\Position;

use App\Portfolio\Dashboard\Dashboard;
use Tests\TestCase;

class DashboardTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        $this->loginWithFakeUser();
    }

    public function testGetStockData_New(): void {
        $scenario_data = $this->prepareScenario();
        $bond_allocations = ['Bond 1' => 33.33, 'Bond 2' => 33.32, 'Bond 3' => 33.35];
        $bond_results = ['Bond 1' => 6.4, 'Bond 2' => 5.3, 'Bond 3' => 7.3];
        $bond_variations = ['Bond 1' => 0.32, 'Bond 2' => 0.26, 'Bond 3' => 0.36];
        $this->translateBondNamesToIdsForKeys($bond_allocations, $scenario_data['bond_names']);
        $this->translateBondNamesToIdsForKeys($bond_results, $scenario_data['bond_names']);
        $this->translateBondNamesToIdsForKeys($bond_variations, $scenario_data['bond_names']);

        $data = Dashboard::getData();

        $this->assertEquals(9000, $data['contributed_amount']);
        $this->assertEquals(9026, $data['updated_amount']);
        $this->assertEquals(11.6, $data['dividends_amount']);
        $this->assertEquals(0.29, $data['overall_variation']);
        $this->assertEquals(33.31, $data['stock_allocation']);
        $this->assertEquals(66.69, $data['bond_allocation']);
        $this->assertEquals([1 => 49.93, 2 => 16.71, 3 => 33.37], $data['stock_type_allocations']);
        $this->assertEquals([1 => 16.71, 2 => 49.93, 3 => 33.37], $data['stock_allocations']);
        $this->assertEquals($bond_allocations, $data['bond_allocations']);
        $this->assertResultsAndVariations([
            'results' => [1 => 2.4, 2 => 1.3, 3 => 3.3],
            'variations' => [1 => 0.48, 2 => 0.09, 3 => 0.33]
        ], $data['stock_positions_list'], 'stock');
        $this->assertResultsAndVariations([
            'results' => $bond_results,
            'variations' => $bond_variations
        ], $data['bond_positions_list'], 'bond');
    }

    private function prepareScenario(): array {
       $this->saveStockPositions([
           ['stock_symbol' => 'SQIA3', 'date' => '2020-07-01', 'contributed_amount' => 1500, 'quantity' => 10, 'amount' => 1501.30],
           ['stock_symbol' => 'BOVA11', 'date' => '2020-07-01', 'contributed_amount' => 500, 'quantity' => 10, 'amount' => 502.40],
           ['stock_symbol' => 'XPML11', 'date' => '2020-07-01', 'contributed_amount' => 1000, 'quantity' => 10, 'amount' => 1003.30],
       ]);
       $this->saveDividendLines([
           ['stock_dividend_id' => 1, 'quantity' => 10, 'amount_paid' => 5.7],
           ['stock_dividend_id' => 2, 'quantity' => 10, 'amount_paid' => 5.9],
       ]);

       $bond_names = $this->saveBondsWithNames([
           ['bond_name' => 'Bond 1'],
           ['bond_name' => 'Bond 2'],
           ['bond_name' => 'Bond 3'],
       ]);
       $bond_positions = [
           ['bond_name' => 'Bond 1', 'date' => '2020-07-01', 'contributed_amount' => 2000, 'amount' => 2006.40],
           ['bond_name' => 'Bond 2', 'date' => '2020-07-01', 'contributed_amount' => 2000, 'amount' => 2005.30],
           ['bond_name' => 'Bond 3', 'date' => '2020-07-01', 'contributed_amount' => 2000, 'amount' => 2007.30],
       ];
       $this->translateBondNamesToIds($bond_positions, $bond_names);
       $this->saveBondPositions($bond_positions);

       return ['bond_names' => $bond_names];
    }

    private function assertResultsAndVariations(array $expected_data, $positions_list, string $product): void {
        $results = [];
        $variations = [];

        foreach ($positions_list as $type => $positions) {
            foreach ($positions as $position) {
                $results[$position[$product . '_id']] = $position['result'];
                $variations[$position[$product . '_id']] = $position['variation'];
            }
        }

        $this->assertEquals($expected_data['results'], $results);
        $this->assertEquals($expected_data['variations'], $variations);
    }
}
