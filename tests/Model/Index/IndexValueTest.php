<?php

namespace Tests\Model\Index;

use App\Model\Index\Index;
use App\Model\Index\IndexValue;
use Carbon\Carbon;
use Tests\TestCase;

class IndexValueTest extends TestCase {

    public function testGetIndexValueForDateRangeWithTwoMissingValuesInRange_ShouldLoadAndReturnValues(): void {
        $index = Index::find(Index::CDI_ID);
        $date = Carbon::parse('2020-06-01');

        $expected_data = [
            0 => ['index_id' => $index->id, 'date' => '2020-06-01', 'value' => 1],
            2 => ['index_id' => $index->id, 'date' => '2020-06-03', 'value' => 1],
            4 => ['index_id' => $index->id, 'date' => '2020-06-05', 'value' => 1],
        ];

        IndexValue::query()->insert($expected_data);

        $expected_data[1] = ['index_id' => $index->id, 'date' => '2020-06-02', 'value' => 0.011345];
        $expected_data[2] = ['index_id' => $index->id, 'date' => '2020-06-03', 'value' => 0.011345];
        $expected_data[3] = ['index_id' => $index->id, 'date' => '2020-06-04', 'value' => 0.011345];

        $index_values = IndexValue::getValuesForDateRange($index, $date, (clone $date)->addDays(5));

        $this->assertIndexValues($expected_data, $index_values);
    }

    public function testGetIndexValueForDateRangeWithOneMissingValueInRange_ShouldLoadAndReturnValues(): void {
        $index = Index::find(Index::SELIC_ID);
        $date = Carbon::parse('2020-01-06');

        $expected_data = [
            ['index_id' => $index->id, 'date' => '2020-01-06', 'value' => 1],
            ['index_id' => $index->id, 'date' => '2020-01-07', 'value' => 1],
            ['index_id' => $index->id, 'date' => '2020-01-08', 'value' => 1],
        ];

        IndexValue::query()->insert($expected_data);

        $expected_data[] = ['index_id' => $index->id, 'date' => '2020-01-09', 'value' => 0.017089];

        $index_values = IndexValue::getValuesForDateRange($index, $date, (clone $date)->addDays(3));

        $this->assertIndexValues($expected_data, $index_values);
    }

    public function testGetIndexValueForDateRangeWithoutStoredPrices_ShouldLoadAndReturnValues(): void {
        $index = Index::find(Index::IPCA_ID);
        $date = Carbon::parse('2020-05-26');

        $expected_data = [
            ['index_id' => $index->id, 'date' => '2020-05-26', 'value' => 0.00940806],
            ['index_id' => $index->id, 'date' => '2020-05-27', 'value' => 0.00940806],
            ['index_id' => $index->id, 'date' => '2020-05-28', 'value' => 0.00940806],
            ['index_id' => $index->id, 'date' => '2020-05-29', 'value' => 0.00940806],
            ['index_id' => $index->id, 'date' => '2020-06-01', 'value' => 0.00738154],
            ['index_id' => $index->id, 'date' => '2020-06-02', 'value' => 0.00738154],
            ['index_id' => $index->id, 'date' => '2020-06-03', 'value' => 0.00738154],
            ['index_id' => $index->id, 'date' => '2020-06-04', 'value' => 0.00738154],
        ];

        $index_values = IndexValue::getValuesForDateRange($index, $date, (clone $date)->addDays(9));

        $this->assertIndexValues($expected_data, $index_values);
    }

    public function testGetIndexValueForDateRangeWithStoredPricesAndIPCA_ShouldNotLoadAndReturnValues(): void {
        $index = Index::find(Index::IPCA_ID);
        $date = Carbon::parse('2020-06-25');

        $expected_data = [
            ['index_id' => $index->id, 'date' => '2020-06-25', 'value' => 1.2345],
            ['index_id' => $index->id, 'date' => '2020-06-26', 'value' => 1.2345],
        ];

        IndexValue::query()->insert($expected_data);

        $index_values = IndexValue::getValuesForDateRange($index, $date, (clone $date)->addDay());

        $this->assertIndexValues($expected_data, $index_values);
    }

    public function testGetIndexValueForDateRangeWithStoredPrices_ShouldNotLoadAndReturnStoredValues(): void {
        $index = Index::find(Index::CDI_ID);
        $date = Carbon::parse('2020-06-01');

        $expected_data = [
            ['index_id' => $index->id, 'date' => '2020-06-01', 'value' => 55],
            ['index_id' => $index->id, 'date' => '2020-06-02', 'value' => 66],
            ['index_id' => $index->id, 'date' => '2020-06-03', 'value' => 77],
        ];

        IndexValue::query()->insert($expected_data);

        $index_values = IndexValue::getValuesForDateRange($index, $date, (clone $date)->addDays(2));

        $this->assertIndexValues($expected_data, $index_values);
    }

    private function assertIndexValues(array $expected_values, array $actual_values): void {
        $this->assertCount(sizeof($expected_values), $actual_values);
        ksort($expected_values);

        foreach ($actual_values as $actual_value) {
            $expected_value = array_shift($expected_values);

            $this->assertEquals($expected_value['index_id'], $actual_value['index_id']);
            $this->assertEquals($expected_value['date'], $actual_value['date']);
            $this->assertEquals($expected_value['value'], $actual_value['value']);
        }
    }
}
