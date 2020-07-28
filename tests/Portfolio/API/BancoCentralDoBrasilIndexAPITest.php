<?php

namespace Tests\Portfolio\API;

use App\Model\Index\Index;
use App\Portfolio\API\BancoCentralDoBrasilAPI;
use Carbon\Carbon;
use Tests\Portfolio\API\Abstracts\IndexAPITest;

class BancoCentralDoBrasilIndexAPITest extends IndexAPITest {

    /**
     * @dataProvider dataProviderTestGetIndexValuesForRange
     */
    public function testGetIndexValuesForRange(int $index_id, Carbon $start_date, Carbon $end_date, array $expected_values): void {
        $index = Index::find($index_id);
        $values = BancoCentralDoBrasilAPI::getIndexValuesForRange($index, $start_date, $end_date);

        $this->assertEquals($expected_values, $values);
    }
}
