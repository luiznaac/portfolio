<?php

namespace Tests\Model\Stock;

use App\Model\Stock\Stock;
use App\Model\Stock\StockPrice;
use Carbon\Carbon;
use Tests\TestCase;

class StockPriceTest extends TestCase {

    public function testStoreWithInvalidDate_ShouldNotStorePrice(): void {
        $stock = new Stock(['symbol' => 'ITSA4']);
        $stock->save();
        $weekend_date = Carbon::parse('2020-07-05');

        $stock_price = new StockPrice();
        $stock_price->loadPriceForDateAndStore($stock, $weekend_date);

        $stored_stock_price = StockPrice::query()
            ->where('date', $weekend_date->toDateString())
            ->where('stock_id', $stock->id)
            ->get()->first();

        $this->assertNull($stored_stock_price);
    }

    public function testGetStockPriceForDate_ShouldLoadFromAPIAndStore(): void {
        $stock = Stock::getStockBySymbol('SQIA3');
        $date = Carbon::parse('2020-02-05');

        $stock_price = new StockPrice();
        $stock_price->loadPriceForDateAndStore($stock, $date);

        $stored_stock_price = StockPrice::query()
            ->where('date', $date->toDateString())
            ->where('stock_id', $stock->id)
            ->get()->first();

        $this->assertEquals($date->toDateString(), $stored_stock_price->date);
        $this->assertEquals(26.48, $stored_stock_price->price);
    }
}
