<?php

namespace Tests\Portfolio\Providers;

use App\Model\Log\Log;
use App\Model\Stock\Stock;
use App\Portfolio\API\DividendAPI;
use App\Portfolio\Providers\StockDividendProvider;
use Carbon\Carbon;
use Tests\TestCase;

class StockDividendProviderTest extends TestCase {

    public function testGetDividendsForRangeWithError_ShouldLogMessage(): void {
        $stock = new Stock();
        $stock->symbol = 'ITSA4';
        $stock->save();

        $start_date = Carbon::parse('2018-08-25');
        $end_date = Carbon::parse('2018-10-05');

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

class DividendAPIForTests implements DividendAPI {

    public static function getDividendsForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        throw new \Exception('test');
    }
}

class StockDividendProviderForTests extends StockDividendProvider {
    protected const DIVIDEND_APIS = [
        DividendAPIForTests::class,
    ];
}
