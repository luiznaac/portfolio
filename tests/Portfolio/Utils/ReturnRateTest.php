<?php

namespace Tests\Portfolio\Utils;

use App\Model\Holiday\Holiday;
use App\Model\Index\Index;
use App\Portfolio\Utils\Calendar;
use App\Portfolio\Utils\ReturnRate;
use Carbon\Carbon;
use Tests\TestCase;

class ReturnRateTest extends TestCase {

    public function dataProviderForTestGetBondNameAndGetReturnRateString(): array {
        return [
            [
                'index_id' => Index::CDI_ID,
                'index_rate' => 105,
                'interest_rate' => null,
                'expected_return_rate_string' => '105% CDI',
            ],
            [
                'index_id' => Index::CDI_ID,
                'index_rate' => 99,
                'interest_rate' => 2,
                'expected_return_rate_string' => '99% CDI + 2%',
            ],
            [
                'index_id' => Index::IPCA_ID,
                'index_rate' => 100,
                'interest_rate' => 2.72,
                'expected_return_rate_string' => '100% IPCA + 2.72%',
            ],
            [
                'index_id' => null,
                'index_rate' => null,
                'interest_rate' => 12,
                'expected_return_rate_string' => '12%',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetBondNameAndGetReturnRateString
     */
    public function testGetBondNameAndGetReturnRateString(
        ?int $index_id,
        ?float $index_rate,
        ?float $interest_rate,
        string $expected_return_rate_string
    ): void {
        $return_rate_string = ReturnRate::getReturnRateString($index_id, $index_rate, $interest_rate);

        $this->assertEquals($expected_return_rate_string, $return_rate_string);
    }
}
