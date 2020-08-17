<?php

namespace Tests\Portfolio\Consolidator;

use App\Model\Bond\Treasury\TreasuryBondPosition;
use App\Model\Index\Index;
use App\Portfolio\Consolidator\TreasuryBondPositionConsolidator;
use Carbon\Carbon;
use Tests\TestCase;

class TreasuryBondPositionConsolidatorTest extends TestCase {

    private $user;

    protected function setUp(): void {
        parent::setUp();
        Carbon::setTestNow('2020-06-30');

        $this->user = $this->loginWithFakeUser();
    }

    /**
     * @dataProvider dataProviderForTestConsolidate
     */
    public function testConsolidate(string $now, array $treasury_bonds, array $treasury_bond_positions, array $orders, array $expected_positions): void {
        $this->setTestNowForB3DateTime($now);
        $treasury_bonds_names = $this->saveTreasuryBondsWithNames($treasury_bonds);
        $this->translateTreasuryBondNamesToIds($treasury_bond_positions, $treasury_bonds_names);
        $this->translateTreasuryBondNamesToIds($orders, $treasury_bonds_names);
        $this->saveTreasuryBondPositions($treasury_bond_positions);
        $this->saveTreasuryBondOrders($orders);
        $this->translateTreasuryBondNamesToIds($expected_positions, $treasury_bonds_names);
        $this->fillUserId($expected_positions);

        TreasuryBondPositionConsolidator::consolidate();

        $this->assertBondPositions(array_reverse($expected_positions));
    }

    public function dataProviderForTestConsolidate(): array {
        return [
            'Without orders - should not create positions' => [
                'now' => '2020-07-03 15:00:00',
                'treasury_bonds' => [
                    ['treasury_bond_name' => 'Bond 1'],
                    ['treasury_bond_name' => 'Bond 2'],
                ],
                'treasury_bond_positions' => [],
                'orders' => [],
                'expected_positions' => [],
            ],
            'Oldest order is not the oldest position - should delete positions and update' => [
                'now' => '2020-07-09 18:00:00',
                'treasury_bonds' => [
                    ['treasury_bond_name' => 'Bond 1', 'index_id' => Index::IPCA_ID, 'interest_rate' => null],
                    ['treasury_bond_name' => 'Bond 2'],
                ],
                'treasury_bond_positions' => [
                    ['treasury_bond_name' => 'Bond 1', 'date' => '2020-07-06', 'amount' => 5000.42, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 1', 'date' => '2020-07-07', 'amount' => 5000.84, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 1', 'date' => '2020-07-08', 'amount' => 5001.27, 'contributed_amount' => 5000],
                ],
                'orders' => [
                    ['treasury_bond_name' => 'Bond 1', 'date' => '2020-07-08', 'type' => 'buy', 'amount' => 5000],
                ],
                'expected_positions' => [
                    ['treasury_bond_name' => 'Bond 1', 'date' => '2020-07-08', 'amount' => 5000.42, 'contributed_amount' => 5000],
                ],
            ],
            'No orders for already consolidated bond - should delete positions' => [
                'now' => '2020-07-07 18:00:00',
                'treasury_bonds' => [
                    ['treasury_bond_name' => 'Bond 1'],
                    ['treasury_bond_name' => 'Bond 2'],
                ],
                'treasury_bond_positions' => [
                    ['treasury_bond_name' => 'Bond 1', 'date' => '2020-07-06', 'amount' => 5000.44, 'contributed_amount' => 5000],
                ],
                'orders' => [],
                'expected_positions' => [],
            ],
            'After market close and new bond in portfolio - should create from order date until now market date' => [
                'now' => '2020-07-13 18:00:00',
                'treasury_bonds' => [
                    ['treasury_bond_name' => 'Bond 1', 'index_id' => Index::SELIC_ID, 'interest_rate' => null],
                    ['treasury_bond_name' => 'Bond 2', 'index_id' => null, 'interest_rate' => 12.5],
                    ['treasury_bond_name' => 'Bond 3', 'index_id' => Index::IPCA_ID, 'interest_rate' => 4],
                ],
                'treasury_bond_positions' => [],
                'orders' => [
                    ['treasury_bond_name' => 'Bond 1', 'date' => '2020-07-06', 'type' => 'buy', 'amount' => 5000],
                    ['treasury_bond_name' => 'Bond 2', 'date' => '2020-07-06', 'type' => 'buy', 'amount' => 5000],
                    ['treasury_bond_name' => 'Bond 3', 'date' => '2020-07-06', 'type' => 'buy', 'amount' => 5000],
                ],
                'expected_positions' => [
                    ['treasury_bond_name' => 'Bond 1', 'date' => '2020-07-06', 'amount' => 5000.42, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 2', 'date' => '2020-07-06', 'amount' => 5002.34, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 3', 'date' => '2020-07-06', 'amount' => 5001.20, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 1', 'date' => '2020-07-07', 'amount' => 5000.84, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 2', 'date' => '2020-07-07', 'amount' => 5004.68, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 3', 'date' => '2020-07-07', 'amount' => 5002.39, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 1', 'date' => '2020-07-08', 'amount' => 5001.27, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 2', 'date' => '2020-07-08', 'amount' => 5007.02, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 3', 'date' => '2020-07-08', 'amount' => 5003.59, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 1', 'date' => '2020-07-09', 'amount' => 5001.69, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 2', 'date' => '2020-07-09', 'amount' => 5009.36, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 3', 'date' => '2020-07-09', 'amount' => 5004.79, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 1', 'date' => '2020-07-10', 'amount' => 5002.11, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 2', 'date' => '2020-07-10', 'amount' => 5011.70, 'contributed_amount' => 5000],
                    ['treasury_bond_name' => 'Bond 3', 'date' => '2020-07-10', 'amount' => 5005.99, 'contributed_amount' => 5000],
                ],
            ],
        ];
    }

    private function fillUserId(array &$expected_positions): void {
        foreach ($expected_positions as &$position) {
            $position['user_id'] = $this->user->id;
        }
    }

    private function assertBondPositions(array $expected_treasury_bond_positions): void {
        $created_treasury_bond_positions = TreasuryBondPosition::getBaseQuery()
            ->whereIn('treasury_bond_id', array_map(function ($position) {
                return $position['treasury_bond_id'];
            }, $expected_treasury_bond_positions))
            ->orderBy('date')->get();

        $this->assertCount(sizeof($expected_treasury_bond_positions), $created_treasury_bond_positions);
        /** @var TreasuryBondPosition $expected_treasury_bond_position */
        foreach ($expected_treasury_bond_positions as $expected_treasury_bond_position) {
            /** @var TreasuryBondPosition $created_treasury_bond_position */
            $created_treasury_bond_position = $created_treasury_bond_positions->pop();

            $this->assertEquals($expected_treasury_bond_position['user_id'], $created_treasury_bond_position->user_id);
            $this->assertEquals($expected_treasury_bond_position['treasury_bond_id'], $created_treasury_bond_position->treasury_bond_id);
            $this->assertEquals($expected_treasury_bond_position['date'], $created_treasury_bond_position->date);
            $this->assertEquals($expected_treasury_bond_position['amount'], $created_treasury_bond_position->amount);
            $this->assertEquals($expected_treasury_bond_position['contributed_amount'], $created_treasury_bond_position->contributed_amount);
        }
    }
}
