<?php

namespace Tests\Portfolio\Providers;

use App\Model\Log\Log;
use App\Portfolio\API\Interfaces\HolidayAPI;
use App\Portfolio\Providers\HolidayProvider;
use Carbon\Carbon;
use Tests\TestCase;

class HolidayProviderTest extends TestCase {

    public function testGetHolidaysForYearWithEmptyArrayAndError_ShouldFallBackToLastOne(): void {
        $date = Carbon::createFromFormat('Y', '2018');

        HolidayProviderForTests::$apis = [
            HolidayAPIForTestsWithError::class,
            HolidayAPIForTestsWithEmptyArray::class,
            HolidayAPIForTests::class
        ];
        $holidays = HolidayProviderForTests::getHolidaysForYear($date);

        $this->assertLog('HolidayProvider::getHolidaysForYear');
        $this->assertEquals(['test' => 'feriadinho'], $holidays);
    }

    public function testGetHolidaysForYearWithEmptyArray_ShouldFallBackToNextOne(): void {
        $date = Carbon::createFromFormat('Y', '2018');

        HolidayProviderForTests::$apis = [
            HolidayAPIForTestsWithEmptyArray::class,
            HolidayAPIForTests::class
        ];
        $holidays = HolidayProviderForTests::getHolidaysForYear($date);

        $this->assertEquals(['test' => 'feriadinho'], $holidays);
    }

    public function testGetHolidaysForYearWithError_ShouldFallBackToNextOne(): void {
        $date = Carbon::createFromFormat('Y', '2018');

        HolidayProviderForTests::$apis = [
            HolidayAPIForTestsWithError::class,
            HolidayAPIForTests::class
        ];
        $holidays = HolidayProviderForTests::getHolidaysForYear($date);

        $this->assertLog('HolidayProvider::getHolidaysForYear');
        $this->assertEquals(['test' => 'feriadinho'], $holidays);
    }

    private function assertLog(string $source): void {
        /** @var Log $log */
        $log = Log::query()->first();

        $this->assertEquals(Log::EXCEPTION_TYPE, $log->type);
        $this->assertEquals($source, $log->source);
        $this->assertEquals('test', $log->message);
    }
}

class HolidayAPIForTestsWithError implements HolidayAPI {

    public static function getHolidaysForYear(Carbon $date): array {
        throw new \Exception('test');
    }
}

class HolidayAPIForTestsWithEmptyArray implements HolidayAPI {

    public static function getHolidaysForYear(Carbon $date): array {
        return [];
    }
}

class HolidayAPIForTests implements HolidayAPI {

    public static function getHolidaysForYear(Carbon $date): array {
        return ['test' => 'feriadinho'];
    }
}

class HolidayProviderForTests extends HolidayProvider {
    public static $apis = [];

    protected static function getAvailableAPIs(): array {
        return self::$apis;
    }
}
