<?php

namespace Tests\Portfolio\Providers;

use App\Model\Log\Log;
use App\Model\Stock\Stock;
use App\Portfolio\API\Interfaces\DividendAPI;
use App\Portfolio\Providers\StockDividendProvider;
use Carbon\Carbon;
use Tests\TestCase;

class StockDividendProviderTest extends TestCase {

    public function testGetDividendsForRangeWithEmptyArrayAndError_ShouldFallBackToLastOne(): void {
        $stock = new Stock();
        $stock->symbol = 'ITSA4';
        $stock->save();

        $start_date = Carbon::parse('2018-08-25');
        $end_date = Carbon::parse('2018-10-05');

        StockDividendProviderForTests::$apis = [
            DividendAPIForTestsWithError::class,
            DividendAPIForTestsWithEmptyArray::class,
            DividendAPIForTests::class
        ];
        $dividends = StockDividendProviderForTests::getDividendsForRange($stock, $start_date, $end_date);

        $this->assertLog('StockDividendProvider::getDividendsForRange');
        $this->assertEquals(['test' => 0.25], $dividends);
    }

    public function testGetDividendsForRangeWithEmptyArray_ShouldFallBackToNextOne(): void {
        $stock = new Stock();
        $stock->symbol = 'ITSA4';
        $stock->save();

        $start_date = Carbon::parse('2018-08-25');
        $end_date = Carbon::parse('2018-10-05');

        StockDividendProviderForTests::$apis = [
            DividendAPIForTestsWithEmptyArray::class,
            DividendAPIForTests::class
        ];
        $dividends = StockDividendProviderForTests::getDividendsForRange($stock, $start_date, $end_date);

        $this->assertEquals(['test' => 0.25], $dividends);
    }

    public function testGetDividendsForRangeWithError_ShouldFallBackToNextOne(): void {
        $stock = new Stock();
        $stock->symbol = 'ITSA4';
        $stock->save();

        $start_date = Carbon::parse('2018-08-25');
        $end_date = Carbon::parse('2018-10-05');

        StockDividendProviderForTests::$apis = [
            DividendAPIForTestsWithError::class,
            DividendAPIForTests::class
        ];
        $dividends = StockDividendProviderForTests::getDividendsForRange($stock, $start_date, $end_date);

        $this->assertLog('StockDividendProvider::getDividendsForRange');
        $this->assertEquals(['test' => 0.25], $dividends);
    }

    public function testGetDividendsForRangeWithError_ShouldLogMessage(): void {
        $stock = new Stock();
        $stock->symbol = 'ITSA4';
        $stock->save();

        $start_date = Carbon::parse('2018-08-25');
        $end_date = Carbon::parse('2018-10-05');

        StockDividendProviderForTests::$apis = [DividendAPIForTestsWithError::class];
        StockDividendProviderForTests::getDividendsForRange($stock, $start_date, $end_date);

        $this->assertLog('StockDividendProvider::getDividendsForRange');
    }

    public function testGetDividendsForRange(): void {
        $stock = new Stock();
        $stock->symbol = 'ITSA4';
        $stock->save();

        $start_date = Carbon::parse('2018-08-25');
        $end_date = Carbon::parse('2018-10-05');

        $dividends = StockDividendProvider::getDividendsForRange($stock, $start_date, $end_date);

        $this->assertEquals([
                '2018-08-30|2018-08-17|JCP' => 0.00960000,
                '2018-08-30|2018-08-17|Dividendo' => 0.19920000,
                '2018-10-01|2018-08-31|Dividendo' => 0.01500000,
            ],
            $dividends
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

class DividendAPIForTestsWithError implements DividendAPI {

    public static function getDividendsForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        throw new \Exception('test');
    }
}

class DividendAPIForTestsWithEmptyArray implements DividendAPI {

    public static function getDividendsForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        return [];
    }
}

class DividendAPIForTests implements DividendAPI {

    public static function getDividendsForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        return ['test' => 0.25];
    }
}

class StockDividendProviderForTests extends StockDividendProvider {
    public static $apis = [];

    protected static function getAvailableAPIs(): array {
        return self::$apis;
    }
}
