<?php

namespace Tests\Model\Bond;

use App\Model\Bond\Bond;
use App\Model\Bond\BondIssuer;
use App\Model\Bond\BondType;
use App\Model\Index\Index;
use Tests\TestCase;

class BondTest extends TestCase {

    public function dataProviderForTestGetBondNameAndGetReturnRateString(): array {
        return [
            'CDB Banco Maxima - 365 days - 105% CDI' => [
                'bond_data' => [
                    'bond_issuer_name' => 'Banco Maxima',
                    'bond_type_id' => BondType::CDB_ID,
                    'index_id' => Index::CDI_ID,
                    'index_rate' => 105,
                    'interest_rate' => null,
                    'days' => '365',
                ],
                'expected_name' => 'CDB Banco Maxima - 365 days',
                'expected_return_rate_string' => '105% CDI',
            ],
            'CDB Banco Semear - JUL/2023 - 99% CDI + 2%' => [
                'bond_data' => [
                    'bond_issuer_name' => 'Banco Semear',
                    'bond_type_id' => BondType::CDB_ID,
                    'index_id' => Index::CDI_ID,
                    'index_rate' => 99,
                    'interest_rate' => 2,
                    'days' => '730',
                ],
                'expected_name' => 'CDB Banco Semear - 730 days',
                'expected_return_rate_string' => '99% CDI + 2%',
            ],
            'CDB Banco Maxima - JUL/2023 - 12%' => [
                'bond_data' => [
                    'bond_issuer_name' => 'Banco Semear',
                    'bond_type_id' => BondType::CDB_ID,
                    'index_id' => null,
                    'index_rate' => null,
                    'interest_rate' => 12,
                    'days' => '1095',
                ],
                'expected_name' => 'CDB Banco Semear - 1095 days',
                'expected_return_rate_string' => '12%',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetBondNameAndGetReturnRateString
     */
    public function testGetBondNameAndGetReturnRateString(
        array $bond_data,
        string $expected_name,
        string $expected_return_rate_string
    ): void {
        $this->createIssuerAndSetId($bond_data);

        /** @var Bond $bond */
        $bond = $this->saveBonds([$bond_data])[0];

        $this->assertEquals($expected_name, $bond->getBondName());
        $this->assertEquals($expected_return_rate_string, $bond->getReturnRateString());
    }

    private function createIssuerAndSetId(array &$bond_data): void {
        $bond_issuer = BondIssuer::query()->create(['name' => $bond_data['bond_issuer_name']]);
        unset($bond_data['bond_issuer_name']);
        $bond_data['bond_issuer_id'] = $bond_issuer->id;
    }
}
