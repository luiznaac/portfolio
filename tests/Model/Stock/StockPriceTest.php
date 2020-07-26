<?php

namespace Tests\Model\Stock;

use App\Model\Stock\Stock;
use App\Model\Stock\StockPrice;
use Carbon\Carbon;
use Tests\TestCase;

class StockPriceTest extends TestCase {

    public function testGetStockPricesForDateRangeWithTwoMissingPricesInRange_ShouldLoadAndReturnValues(): void {
        $stock = Stock::getStockBySymbol('SQIA3');
        Carbon::setTestNow('2020-05-31');

        $expected_data = [
            0 => ['stock_id' => $stock->id, 'date' => Carbon::today()->addDay()->toDateString(), 'price' => 999.99],
            2 => ['stock_id' => $stock->id, 'date' => Carbon::today()->addDays(3)->toDateString(), 'price' => 888.99],
            4 => ['stock_id' => $stock->id, 'date' => Carbon::today()->addDays(5)->toDateString(), 'price' => 777.99],
        ];

        StockPrice::query()->insert($expected_data);

        $expected_data[1] = ['stock_id' => $stock->id, 'date' => Carbon::today()->addDays(2)->toDateString(), 'price' => 20.52];
        $expected_data[2] = ['stock_id' => $stock->id, 'date' => Carbon::today()->addDays(3)->toDateString(), 'price' => 20.68];
        $expected_data[3] = ['stock_id' => $stock->id, 'date' => Carbon::today()->addDays(4)->toDateString(), 'price' => 20.39];

        $stock_prices = StockPrice::getStockPricesForDateRange($stock, Carbon::today(), Carbon::today()->addDays(5));

        $this->assertStockPrices($expected_data, $stock_prices);
    }

    public function testGetStockPricesForDateRangeWithOneMissingPriceInRange_ShouldLoadAndReturnValues(): void {
        $stock = Stock::getStockBySymbol('SQIA3');
        Carbon::setTestNow('2020-05-31');

        $expected_data = [
            ['stock_id' => $stock->id, 'date' => Carbon::today()->addDay()->toDateString(), 'price' => 999.99],
            ['stock_id' => $stock->id, 'date' => Carbon::today()->addDays(2)->toDateString(), 'price' => 888.99],
            ['stock_id' => $stock->id, 'date' => Carbon::today()->addDays(3)->toDateString(), 'price' => 777.99],
        ];

        StockPrice::query()->insert($expected_data);

        $expected_data[] = ['stock_id' => $stock->id, 'date' => Carbon::today()->addDays(4)->toDateString(), 'price' => 20.39];

        $stock_prices = StockPrice::getStockPricesForDateRange($stock, Carbon::today(), Carbon::today()->addDays(4));

        $this->assertStockPrices($expected_data, $stock_prices);
    }

    public function testGetStockPricesForDateRangeWithoutStoredPrices_ShouldLoadAndReturnValues(): void {
        $stock = Stock::getStockBySymbol('SQIA3');
        Carbon::setTestNow('2020-05-31');

        $expected_data = [
            ['stock_id' => $stock->id, 'date' => Carbon::today()->addDay()->toDateString(), 'price' => 19.95],
            ['stock_id' => $stock->id, 'date' => Carbon::today()->addDays(2)->toDateString(), 'price' => 20.52],
            ['stock_id' => $stock->id, 'date' => Carbon::today()->addDays(3)->toDateString(), 'price' => 20.68],
        ];

        $stock_prices = StockPrice::getStockPricesForDateRange($stock, Carbon::today(), Carbon::today()->addDays(3));

        $this->assertStockPrices($expected_data, $stock_prices);
    }

    public function testGetStockPricesForDateRangeWithStoredPrices_ShouldNotLoadAndReturnStoredValues(): void {
        $stock = Stock::getStockBySymbol('SQIA3');
        Carbon::setTestNow('2020-05-31');

        $expected_data = [
            ['stock_id' => $stock->id, 'date' => Carbon::today()->addDay()->toDateString(), 'price' => 999.99],
            ['stock_id' => $stock->id, 'date' => Carbon::today()->addDays(2)->toDateString(), 'price' => 888.99],
            ['stock_id' => $stock->id, 'date' => Carbon::today()->addDays(3)->toDateString(), 'price' => 777.99],
        ];

        StockPrice::query()->insert($expected_data);

        $stock_prices = StockPrice::getStockPricesForDateRange($stock, Carbon::today(), Carbon::today()->addDays(3));

        $this->assertStockPrices($expected_data, $stock_prices);
    }

    private function assertStockPrices(array $expected_prices, array $actual_prices): void {
        $this->assertCount(sizeof($expected_prices), $actual_prices);
        ksort($expected_prices);

        foreach ($actual_prices as $actual_price) {
            $expected_price = array_shift($expected_prices);

            $this->assertEquals($expected_price['stock_id'], $actual_price['stock_id']);
            $this->assertEquals($expected_price['date'], $actual_price['date']);
            $this->assertEquals($expected_price['price'], $actual_price['price']);
        }
    }
}
