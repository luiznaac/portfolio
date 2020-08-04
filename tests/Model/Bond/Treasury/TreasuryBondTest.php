<?php

namespace Tests\Model\Bond\Treasury;

use App\Model\Bond\Treasury\TreasuryBond;
use App\Model\Index\Index;
use Tests\TestCase;

class TreasuryBondTest extends TestCase {

    public function dataProviderForTestGetTreasuryBondNameAndGetReturnRateString(): array {
        return [
            'Tesouro Prefixado 2026' => [
                'bond_data' => [
                    'interest_rate' => 5.8,
                    'maturity_date' => '2026-01-01',
                ],
                'expected_name' => 'Tesouro Prefixado 2026',
            ],
            'Tesouro IPCA+ 2035' => [
                'bond_data' => [
                    'index_id' => Index::IPCA_ID,
                    'interest_rate' => 3.44,
                    'maturity_date' => '2035-05-15',
                ],
                'expected_name' => 'Tesouro IPCA+ 2035',
            ],
            'Tesouro Selic 2025' => [
                'bond_data' => [
                    'index_id' => Index::SELIC_ID,
                    'interest_rate' => 0.0344,
                    'maturity_date' => '2025-01-01',
                ],
                'expected_name' => 'Tesouro Selic 2025',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetTreasuryBondNameAndGetReturnRateString
     */
    public function testGetBondNameAndGetReturnRateString(
        array $treasury_bond_data,
        string $expected_name
    ): void {
        /** @var TreasuryBond $treasury_bond */
        $treasury_bond = $this->saveTreasuryBonds([$treasury_bond_data])[0];

        $this->assertEquals($expected_name, $treasury_bond->getTreasuryBondName());
    }
}
