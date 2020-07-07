<?php

namespace Tests\Model\Stock;

use App\Model\Stock\Stock;
use App\Model\Stock\StockPrice;
use Carbon\Carbon;
use Tests\TestCase;

class StockTest extends TestCase {

    public function testGetStockPriceForDate_ShouldGetStoredInfo(): void {
        $stock = new Stock();
        $stock->store('BOVA11');

        $date = Carbon::parse('2020-06-26');
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
        $stock = new Stock();
        $stock->store('BOVA11');

        $date = Carbon::createFromFormat('Y-m-d', '2020-06-26');
        $price = $stock->getStockPriceForDate($date);

        /** @var StockPrice $stock_price */
        $stock_price = $stock->getStockPrices()->first();

        $this->assertEquals($date->toDateString(), $stock_price->date);
        $this->assertEquals(90.22, $stock_price->price);
        $this->assertEquals(90.22, $price);
    }

    public function testStoreStock_ShouldNotLoadName(): void {
        $stock = new Stock();
        $stock->store('BOVA11');

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
