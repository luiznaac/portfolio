<?php

namespace Tests\Portfolio\Providers;

use App\Model\Log\Log;
use App\Model\Stock\Stock;
use App\Portfolio\API\PriceAPI;
use App\Portfolio\Providers\StockPriceProvider;
use Carbon\Carbon;
use Tests\TestCase;

class StockPriceProviderTest extends TestCase {

    public function testGetPriceForDateWithErrorOnFirstAPI_ShouldFallbackToTheNextOne(): void {
        $stock = new Stock();
        $stock->symbol = 'FLRY3';
        $stock->save();

        $date = Carbon::parse('2020-07-13');

        StockPriceProviderForTests::$apis = [
            PriceAPIForTestsWithError::class,
            PriceAPIForTests::class
        ];
        $price = StockPriceProviderForTests::getPriceForDate($stock, $date);

        $this->assertLog('StockPriceProvider::getPriceForDate');
        $this->assertEquals(123.45, $price);
    }

    public function testGetPriceForDateWithError_ShouldLogMessage(): void {
        $stock = Stock::getStockBySymbol('SQIA3');
        $date = Carbon::parse('2020-07-02');

        StockPriceProviderForTests::$apis = [PriceAPIForTestsWithError::class];
        StockPriceProviderForTests::getPriceForDate($stock, $date);

        $this->assertLog('StockPriceProvider::getPriceForDate');
    }

    public function testGetPricesForRangeWithEmptyArrayAndError_ShouldFallbackToTheLastOne(): void {
        $stock = new Stock();
        $stock->symbol = 'FLRY3';
        $stock->save();

        $start_date = Carbon::parse('2020-07-13');
        $end_date = Carbon::parse('2020-07-13');

        StockPriceProviderForTests::$apis = [
            PriceAPIForTestsWithError::class,
            PriceAPIForTestsWithEmptyArray::class,
            PriceAPIForTests::class
        ];
        $prices = StockPriceProviderForTests::getPricesForRange($stock, $start_date, $end_date);

        $this->assertLog('StockPriceProvider::getPricesForRange');
        $this->assertEquals([
            '2020-07-13' => 123.45,
        ],
            $prices);
    }

    public function testGetPricesForRangeWithEmptyArrayOnFirstAPI_ShouldFallbackToTheNextOne(): void {
        $stock = new Stock();
        $stock->symbol = 'FLRY3';
        $stock->save();

        $start_date = Carbon::parse('2020-07-13');
        $end_date = Carbon::parse('2020-07-13');

        StockPriceProviderForTests::$apis = [
            PriceAPIForTestsWithEmptyArray::class,
            PriceAPIForTests::class
        ];
        $prices = StockPriceProviderForTests::getPricesForRange($stock, $start_date, $end_date);

        $this->assertEquals([
            '2020-07-13' => 123.45,
        ],
            $prices);
    }

    public function testGetPricesForRangeWithErrorOnFirstAPI_ShouldFallbackToTheNextOne(): void {
        $stock = new Stock();
        $stock->symbol = 'FLRY3';
        $stock->save();

        $start_date = Carbon::parse('2020-07-13');
        $end_date = Carbon::parse('2020-07-13');

        StockPriceProviderForTests::$apis = [
            PriceAPIForTestsWithError::class,
            PriceAPIForTests::class
        ];
        $prices = StockPriceProviderForTests::getPricesForRange($stock, $start_date, $end_date);

        $this->assertLog('StockPriceProvider::getPricesForRange');
        $this->assertEquals([
            '2020-07-13' => 123.45,
        ],
            $prices);
    }

    public function testGetPricesForRangeWithError_ShouldLogMessage(): void {
        $stock = Stock::getStockBySymbol('SQIA3');
        $start_date = Carbon::parse('2020-07-02');
        $end_date = Carbon::parse('2020-07-06');

        StockPriceProviderForTests::$apis = [PriceAPIForTestsWithError::class];
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

class PriceAPIForTestsWithError implements PriceAPI {

    public static function getPricesForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        throw new \Exception('test');
    }

    public static function getPriceForDate(Stock $stock, Carbon $date): float {
        throw new \Exception('test');
    }
}

class PriceAPIForTestsWithEmptyArray implements PriceAPI {

    public static function getPricesForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        return [];
    }

    public static function getPriceForDate(Stock $stock, Carbon $date): float {
    }
}

class PriceAPIForTests implements PriceAPI {

    public static function getPricesForRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        return ['2020-07-13' => 123.45];
    }

    public static function getPriceForDate(Stock $stock, Carbon $date): float {
        return 123.45;
    }
}

class StockPriceProviderForTests extends StockPriceProvider {

    public static $apis = [];

    protected static function getAvailableAPIs(): array {
        return self::$apis;
    }
}
