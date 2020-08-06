<?php

namespace Tests\Portfolio\Consolidator;

use App\Model\Bond\BondPosition;
use App\Model\Index\Index;
use App\Portfolio\Consolidator\BondPositionConsolidator;
use Carbon\Carbon;
use Tests\TestCase;

class BondPositionConsolidatorTest extends TestCase {

    private $user;

    protected function setUp(): void {
        parent::setUp();
        Carbon::setTestNow('2020-06-30');

        $this->user = $this->loginWithFakeUser();
    }

    /**
     * @dataProvider dataProviderForTestConsolidate
     */
    public function testConsolidate(string $now, array $bonds, array $bond_positions, array $orders, array $expected_positions): void {
        $this->setTestNowForB3DateTime($now);
        $bonds_names = $this->saveBondsWithNames($bonds);
        $this->translateBondNamesToIds($orders, $bonds_names);
        $bond_orders_names = $this->saveBondOrdersWithNames($orders);
        $this->translateBondOrderNamesToIds($bond_positions, $bond_orders_names);
        $this->saveBondPositions($bond_positions);
        $this->translateBondOrderNamesToIds($expected_positions, $bond_orders_names);
        $this->fillUserId($expected_positions);

        BondPositionConsolidator::consolidate();

        $this->assertBondPositions(array_reverse($expected_positions));
    }

    public function dataProviderForTestConsolidate(): array {
        return [
            'Without orders - should not create positions' => [
                'now' => '2020-07-03 15:00:00',
                'bonds' => [
                    ['bond_name' => 'Bond 1'],
                    ['bond_name' => 'Bond 2'],
                ],
                'bond_positions' => [],
                'orders' => [],
                'expected_positions' => [],
            ],
            'After market close and new bond in portfolio - should create from order date until now market date' => [
                'now' => '2020-07-13 18:00:00',
                'bonds' => [
                    ['bond_name' => 'Bond 1', 'index_id' => Index::CDI_ID, 'index_rate' => 105, 'interest_rate' => null],
                    ['bond_name' => 'Bond 2', 'index_id' => null, 'index_rate' => null, 'interest_rate' => 12.5],
                    ['bond_name' => 'Bond 3', 'index_id' => Index::SELIC_ID, 'index_rate' => 80, 'interest_rate' => 4],
                ],
                'bond_positions' => [],
                'orders' => [
                    ['bond_order_name' => 'Bond Order 1', 'bond_name' => 'Bond 1', 'date' => '2020-07-06', 'type' => 'buy', 'amount' => 5000],
                    ['bond_order_name' => 'Bond Order 2', 'bond_name' => 'Bond 2', 'date' => '2020-07-06', 'type' => 'buy', 'amount' => 5000],
                    ['bond_order_name' => 'Bond Order 3', 'bond_name' => 'Bond 3', 'date' => '2020-07-06', 'type' => 'buy', 'amount' => 5000],
                ],
                'expected_positions' => [
                    ['bond_order_name' => 'Bond Order 1', 'date' => '2020-07-06', 'amount' => 5000.44, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 2', 'date' => '2020-07-06', 'amount' => 5002.34, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 3', 'date' => '2020-07-06', 'amount' => 5001.12, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 1', 'date' => '2020-07-07', 'amount' => 5000.89, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 2', 'date' => '2020-07-07', 'amount' => 5004.68, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 3', 'date' => '2020-07-07', 'amount' => 5002.23, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 1', 'date' => '2020-07-08', 'amount' => 5001.33, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 2', 'date' => '2020-07-08', 'amount' => 5007.02, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 3', 'date' => '2020-07-08', 'amount' => 5003.35, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 1', 'date' => '2020-07-09', 'amount' => 5001.77, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 2', 'date' => '2020-07-09', 'amount' => 5009.36, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 3', 'date' => '2020-07-09', 'amount' => 5004.47, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 1', 'date' => '2020-07-10', 'amount' => 5002.22, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 2', 'date' => '2020-07-10', 'amount' => 5011.70, 'contributed_amount' => 5000],
                    ['bond_order_name' => 'Bond Order 3', 'date' => '2020-07-10', 'amount' => 5005.58, 'contributed_amount' => 5000],
                ],
            ],
        ];
    }

    private function fillUserId(array &$expected_positions): void {
        foreach ($expected_positions as &$position) {
            $position['user_id'] = $this->user->id;
        }
    }

    private function assertBondPositions(array $expected_bond_positions): void {
        $created_bond_positions = BondPosition::getBaseQuery()
            ->whereIn('bond_order_id', array_map(function ($position) {
                return $position['bond_order_id'];
            }, $expected_bond_positions))
            ->orderBy('date')->get();

        $this->assertCount(sizeof($expected_bond_positions), $created_bond_positions);
        /** @var BondPosition $expected_bond_position */
        foreach ($expected_bond_positions as $expected_bond_position) {
            /** @var BondPosition $created_bond_position */
            $created_bond_position = $created_bond_positions->pop();

            $this->assertEquals($expected_bond_position['user_id'], $created_bond_position->user_id);
            $this->assertEquals($expected_bond_position['bond_order_id'], $created_bond_position->bond_order_id);
            $this->assertEquals($expected_bond_position['date'], $created_bond_position->date);
            $this->assertEquals($expected_bond_position['amount'], $created_bond_position->amount);
            $this->assertEquals($expected_bond_position['contributed_amount'], $created_bond_position->contributed_amount);
        }
    }
}
