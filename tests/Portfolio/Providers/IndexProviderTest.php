<?php

namespace Tests\Portfolio\Providers;

use App\Model\Index\Index;
use App\Model\Log\Log;
use App\Portfolio\API\Interfaces\IndexAPI;
use App\Portfolio\Providers\IndexProvider;
use Carbon\Carbon;
use Tests\TestCase;

class IndexProviderTest extends TestCase {

    public function testGetIndexValuesForRangeWithEmptyArrayAndError_ShouldFallBackToLastOne(): void {
        $index = Index::find(Index::IPCA_ID);
        $start_date = Carbon::parse('2018-08-25');
        $end_date = Carbon::parse('2018-10-05');

        IndexProviderForTests::$apis = [
            IndexAPIForTestsWithError::class,
            IndexAPIForTestsWithEmptyArray::class,
            IndexAPIForTests::class
        ];
        $values = IndexProviderForTests::getIndexValuesForRange($index, $start_date, $end_date);

        $this->assertLog('IndexProvider::getIndexValuesForRange');
        $this->assertEquals(['test' => 0.025], $values);
    }

    public function testGetIndexValuesForRangeWithEmptyArray_ShouldFallBackToNextOne(): void {
        $index = Index::find(Index::CDI_ID);
        $start_date = Carbon::parse('2018-08-25');
        $end_date = Carbon::parse('2018-10-05');

        IndexProviderForTests::$apis = [
            IndexAPIForTestsWithEmptyArray::class,
            IndexAPIForTests::class
        ];
        $values = IndexProviderForTests::getIndexValuesForRange($index, $start_date, $end_date);

        $this->assertEquals(['test' => 0.025], $values);
    }

    public function testGetIndexValuesForRangeWithError_ShouldFallBackToNextOne(): void {
        $index = Index::find(Index::CDI_ID);
        $start_date = Carbon::parse('2018-08-25');
        $end_date = Carbon::parse('2018-10-05');

        IndexProviderForTests::$apis = [
            IndexAPIForTestsWithError::class,
            IndexAPIForTests::class
        ];
        $values = IndexProviderForTests::getIndexValuesForRange($index, $start_date, $end_date);

        $this->assertLog('IndexProvider::getIndexValuesForRange');
        $this->assertEquals(['test' => 0.025], $values);
    }

    public function testGetIndexValuesForRangeWithError_ShouldLogMessage(): void {
        $index = Index::find(Index::CDI_ID);
        $start_date = Carbon::parse('2018-08-25');
        $end_date = Carbon::parse('2018-10-05');

        IndexProviderForTests::$apis = [IndexAPIForTestsWithError::class];
        IndexProviderForTests::getIndexValuesForRange($index, $start_date, $end_date);

        $this->assertLog('IndexProvider::getIndexValuesForRange');
    }

    public function testGetIndexValuesForRange(): void {
        $index = Index::find(Index::SELIC_ID);
        $start_date = Carbon::parse('2018-08-27');
        $end_date = Carbon::parse('2018-08-29');

        $values = IndexProvider::getIndexValuesForRange($index, $start_date, $end_date);

        $this->assertEquals([
                '2018-08-27' => 0.024620,
                '2018-08-28' => 0.024620,
                '2018-08-29' => 0.024620,
            ],
            $values
        );
    }

    private function assertLog(string $source): void {
        /** @var Log $log */
        $log = Log::query()->first();

        $this->assertEquals(Log::EXCEPTION_TYPE, $log->type);
        $this->assertEquals($source, $log->source);
        $this->assertEquals('test', $log->message);
    }
}

class IndexAPIForTestsWithError implements IndexAPI {

    public static function getIndexValuesForRange(Index $index, Carbon $start_date, Carbon $end_date): array {
        throw new \Exception('test');
    }
}

class IndexAPIForTestsWithEmptyArray implements IndexAPI {

    public static function getIndexValuesForRange(Index $index, Carbon $start_date, Carbon $end_date): array {
        return [];
    }
}

class IndexAPIForTests implements IndexAPI {

    public static function getIndexValuesForRange(Index $index, Carbon $start_date, Carbon $end_date): array {
        return ['test' => 0.025];
    }
}

class IndexProviderForTests extends IndexProvider {
    public static $apis = [];

    protected static function getAvailableAPIs(): array {
        return self::$apis;
    }
}
