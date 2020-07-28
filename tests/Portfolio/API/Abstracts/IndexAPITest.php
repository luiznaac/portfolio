<?php

namespace Tests\Portfolio\API\Abstracts;

use App\Model\Index\Index;
use Carbon\Carbon;
use Tests\TestCase;

abstract class IndexAPITest extends TestCase {

    public function dataProviderTestGetIndexValuesForRange(): array {
        return [
            'Selic' => [
                'index' => Index::SELIC_ID,
                'start_date' => Carbon::parse('2020-06-16'),
                'end_date' => Carbon::parse('2020-06-19'),
                'expected_values' => [
                    '2020-06-16' => 0.011345,
                    '2020-06-17' => 0.011345,
                    '2020-06-18' => 0.008442,
                    '2020-06-19' => 0.008442,
                ]
            ],
            'CDI' => [
                'index' => Index::CDI_ID,
                'start_date' => Carbon::parse('2020-03-17'),
                'end_date' => Carbon::parse('2020-03-20'),
                'expected_values' => [
                    '2020-03-17' => 0.016137,
                    '2020-03-18' => 0.016137,
                    '2020-03-19' => 0.014227,
                    '2020-03-20' => 0.014227,
                ]
            ],
            'IPCA' => [
                'index' => Index::IPCA_ID,
                'start_date' => Carbon::parse('2020-01-01'),
                'end_date' => Carbon::parse('2020-06-19'),
                'expected_values' => [
                    '2020-01-01' => 0.21,
                    '2020-02-01' => 0.25,
                    '2020-03-01' => 0.07,
                    '2020-04-01' => -0.31,
                    '2020-05-01' => -0.38,
                    '2020-06-01' => 0.26,
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestGetIndexValuesForRange
     */
    abstract public function testGetIndexValuesForRange(int $index_id, Carbon $start_date, Carbon $end_date, array $expected_values): void;
}
