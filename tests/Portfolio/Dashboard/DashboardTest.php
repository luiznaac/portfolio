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
        $bond_allocations = ['Bond 1' => 19.06, 'Bond 2' => 19.05, 'Bond 3' => 19.07];
        $bond_results = ['Bond 1' => 6.4, 'Bond 2' => 5.3, 'Bond 3' => 7.3];
        $bond_variations = ['Bond 1' => 0.32, 'Bond 2' => 0.26, 'Bond 3' => 0.36];
        $this->translateBondOrderNamesToIdsForKeys($bond_allocations, $scenario_data['bond_names']);
        $this->translateBondOrderNamesToIdsForKeys($bond_results, $scenario_data['bond_names']);
        $this->translateBondOrderNamesToIdsForKeys($bond_variations, $scenario_data['bond_names']);

        $treasury_bond_allocations = ['Treasury Bond 1' => 14.28, 'Treasury Bond 2' => 14.27, 'Treasury Bond 3' => 14.26];
        $treasury_bond_results = ['Treasury Bond 1' => 3.4, 'Treasury Bond 2' => 2.3, 'Treasury Bond 3' => 1.3];
        $treasury_bond_variations = ['Treasury Bond 1' => 0.23, 'Treasury Bond 2' => 0.15, 'Treasury Bond 3' => 0.09];
        $this->translateTreasuryBondNamesToIdsForKeys($treasury_bond_allocations, $scenario_data['treasury_bond_names']);
        $this->translateTreasuryBondNamesToIdsForKeys($treasury_bond_results, $scenario_data['treasury_bond_names']);
        $this->translateTreasuryBondNamesToIdsForKeys($treasury_bond_variations, $scenario_data['treasury_bond_names']);

        $data = Dashboard::getData();

        $this->assertEquals(13500, $data['contributed_amount']);
        $this->assertEquals(13533, $data['updated_amount']);
        $this->assertEquals(11.6, $data['dividends_amount']);
        $this->assertEquals(0.24, $data['overall_variation']);
        $this->assertEquals(22.22, $data['stock_allocation']);
        $this->assertEquals(77.78, $data['bond_allocation']);
        $this->assertEquals([1 => 49.93, 2 => 16.71, 3 => 33.37], $data['stock_type_allocations']);
        $this->assertEquals([1 => 16.71, 2 => 49.93, 3 => 33.37], $data['stock_allocations']);
        $this->assertEquals($bond_allocations, $data['bond_allocations']['bonds']);
        $this->assertEquals($treasury_bond_allocations, $data['bond_allocations']['treasury_bonds']);
        $this->assertResultsAndVariations([
            'results' => [1 => 2.4, 2 => 1.3, 3 => 3.3],
            'variations' => [1 => 0.48, 2 => 0.09, 3 => 0.33]
        ], $data['stock_positions_list'], 'stock');
        $this->assertResultsAndVariations([
            'results' => $bond_results,
            'variations' => $bond_variations
        ], $data['bond_positions_list'], 'bond');
        $this->assertResultsAndVariations([
            'results' => $treasury_bond_results,
            'variations' => $treasury_bond_variations
        ], $data['bond_positions_list'], 'treasury_bond');
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
        $bond_orders = [
            ['bond_order_name' => 'Bond Order 1', 'bond_name' => 'Bond 1'],
            ['bond_order_name' => 'Bond Order 2', 'bond_name' => 'Bond 2'],
            ['bond_order_name' => 'Bond Order 3', 'bond_name' => 'Bond 3'],
        ];
        $this->translateBondNamesToIds($bond_orders, $bond_names);
        $bond_orders_names = $this->saveBondOrdersWithNames($bond_orders);
        $bond_positions = [
           ['bond_order_name' => 'Bond Order 1', 'date' => '2020-07-01', 'contributed_amount' => 2000, 'amount' => 2006.40],
           ['bond_order_name' => 'Bond Order 2', 'date' => '2020-07-01', 'contributed_amount' => 2000, 'amount' => 2005.30],
           ['bond_order_name' => 'Bond Order 3', 'date' => '2020-07-01', 'contributed_amount' => 2000, 'amount' => 2007.30],
        ];
        $this->translateBondOrderNamesToIds($bond_positions, $bond_orders_names);
        $this->saveBondPositions($bond_positions);

        $treasury_bond_names = $this->saveTreasuryBondsWithNames([
            ['treasury_bond_name' => 'Treasury Bond 1'],
            ['treasury_bond_name' => 'Treasury Bond 2'],
            ['treasury_bond_name' => 'Treasury Bond 3'],
        ]);
        $treasury_bond_positions = [
            ['treasury_bond_name' => 'Treasury Bond 1', 'date' => '2020-07-01', 'contributed_amount' => 1500, 'amount' => 1503.40],
            ['treasury_bond_name' => 'Treasury Bond 2', 'date' => '2020-07-01', 'contributed_amount' => 1500, 'amount' => 1502.30],
            ['treasury_bond_name' => 'Treasury Bond 3', 'date' => '2020-07-01', 'contributed_amount' => 1500, 'amount' => 1501.30],
        ];
        $this->translateTreasuryBondNamesToIds($treasury_bond_positions, $treasury_bond_names);
        $this->saveTreasuryBondPositions($treasury_bond_positions);

       return [
           'bond_names' => $bond_names,
           'treasury_bond_names' => $treasury_bond_names,
       ];
    }

    private function assertResultsAndVariations(array $expected_data, $positions_list, string $product): void {
        $results = [];
        $variations = [];

        foreach ($positions_list as $type => $positions) {
            foreach ($positions as $position) {
                if(!isset($position[$product . '_id'])) {
                    continue;
                }

                $results[$position[$product . '_id']] = $position['result'];
                $variations[$position[$product . '_id']] = $position['variation'];
            }
        }

        $this->assertEquals($expected_data['results'], $results);
        $this->assertEquals($expected_data['variations'], $variations);
    }
}
