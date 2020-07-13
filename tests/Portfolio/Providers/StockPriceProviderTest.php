<?php

namespace Tests\Portfolio\Providers;

use App\Model\Log\Log;
use App\Model\Stock\Stock;
use App\Portfolio\API\PriceAPI;
use App\Portfolio\Providers\StockPriceProvider;
use Carbon\Carbon;
use Tests\TestCase;

class StockPriceProviderTest extends TestCase {

    public function testGetPriceForDateWithError_ShouldLogMessage(): void {
        $stock = Stock::getStockBySymbol('SQIA3');
        $date = Carbon::parse('2020-07-02');

        StockPriceProviderForTests::getPriceForDate($stock, $date);

        $this->assertLog('StockPriceProvider::getPriceForDate');
    }

    public function testGetPricesForRangeWithError_ShouldLogMessage(): void {
        $stock = Stock::getStockBySymbol('SQIA3');
        $start_date = Carbon::parse('2020-07-02');
        $end_date = Carbon::parse('2020-07-06');

        StockPriceProviderForTests::getPricesForRange($stock, $start_date, $end_date);

        $this->assertLog('StockPriceProvider::getPricesForRange');
    }

    public function testGetPricesForRange(): void {
        $stock = new Stock();
        $stock->symbol = 'IVVB11';
        $stock->save();

        $start_date = Carbon::parse('2020-07-02');
        $end_date = Carbon::parse('2020-07-06');

        $prices = StockPriceProvider::getPricesForRange($stock, $start_date, $end_date);

        $this->assertEquals([
                '2020-07-02' => 181.24,
                '2020-07-03' => 182.00,
                '2020-07-06' => 183.45,
            ],
            $prices);
    }

    public function testGetPriceForDate(): void {
        $stock = new Stock();
        $stock->symbol = 'IVVB11';
        $stock->save();

        $price = StockPriceProvider::getPriceForDate($stock, Carbon::parse('2020-07-07'));

        $this->assertEquals(182.60, $price);
    }

    private function assertLog(string $source): void {
        /** @var Log $log */
        $log = Log::query()->first();

        $this->assertEquals(Log::EXCEPTION_TYPE, $log->type);
        $this->assertEquals($source, $log->source);
        $this->assertEquals('test', $log->message);
    }
}

class PriceAPIForTests implements PriceAPI {

    public static function getPricesForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        throw new \Exception('test');
    }

    public static function getPriceForDate(Stock $stock, Carbon $date): float {
        throw new \Exception('test');
    }
}

class StockPriceProviderForTests extends StockPriceProvider {
    protected const PRICE_APIS = [
        PriceAPIForTests::class,
    ];
}
