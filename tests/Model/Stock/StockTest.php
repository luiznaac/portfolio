<?php

namespace Tests\Model\Stock;

use App\Model\Stock\Stock;
use App\Model\Stock\StockPrice;
use Carbon\Carbon;
use Tests\TestCase;

class StockTest extends TestCase {

    public function testGetStockPriceForDateWithInvalidDate_ShouldReturnNull(): void {
        $stock = Stock::getStockBySymbol('SQIA3');
        $weekend_date = Carbon::parse('2020-07-05');

        $price = $stock->getStockPriceForDate($weekend_date);

        $this->assertNull($price);
    }

    public function testGetStockPriceForDate_ShouldGetStoredInfo(): void {
        $stock = Stock::getStockBySymbol('BOVA11');

        $date = Carbon::parse('2020-05-07');
        $expected_price = 123.45;

        $stock_price = new StockPrice();
        $stock_price->stock_id = $stock->id;
        $stock_price->date = $date->toDateString();
        $stock_price->price = $expected_price;
        $stock_price->save();

        $actual_price = $stock->getStockPriceForDate($date);

        $this->assertEquals($expected_price, $actual_price);
    }

    public function testGetStockPriceForDate_ShouldLoadFromAPIAndStore(): void {
        $stock = Stock::getStockBySymbol('SQIA3');

        $date = Carbon::createFromFormat('Y-m-d', '2020-04-29');
        $price = $stock->getStockPriceForDate($date);

        /** @var StockPrice $stock_price */
        $stock_price = $stock->getStockPrices()->first();

        $this->assertEquals($date->toDateString(), $stock_price->date);
        $this->assertEquals(20.29, $stock_price->price);
        $this->assertEquals(20.29, $price);
    }

    public function testStoreStock_ShouldNotLoadName(): void {
        $stock = new Stock();
        $stock->store('ITSA4');

        $this->assertNull($stock->name);
    }

    public function testLoadStockName_StockNotFound_ShouldStoreNull(): void {
        $stock = new Stock();
        $stock->store('XXXXXX');
        $stock->loadStockName();

        $this->assertNull($stock->name);
    }

    public function testLoadStockName_ShouldLoadNameFromAlphaVantageAPI(): void {
        $stock = new Stock();
        $stock->store('ITSA4');
        $stock->loadStockName();

        $this->assertEquals('Itausa - Investimentos Itau S.A.', $stock->name);
    }
}
