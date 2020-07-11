<?php

namespace Tests\Model\Stock;

use App\Model\Stock\Stock;
use App\Model\Stock\StockPrice;
use App\Model\Stock\StockType;
use Carbon\Carbon;
use Tests\TestCase;

class StockTest extends TestCase {

    public function testUpdateInfosForAllStocks(): void {
        $stock = new Stock();
        $stock->symbol = 'FLRY3';
        $stock->save();

        Stock::updateInfosForAllStocks();
        $stock->refresh();

        $this->assertEquals(StockType::ACAO_ID, $stock->stock_type_id);
        $this->assertEquals('Fleury S.A.', $stock->name);
    }

    public function testGetStockTypeWhenTypeNotFound_ShouldReturnDefaultAcao(): void {
        $stock = new Stock();
        $stock->symbol = 'XXXXX';
        $actual_stock_type = $stock->getStockType();
        $expected_stock_type = StockType::getStockTypeByType(StockType::ACAO_TYPE);

        $this->assertEquals($expected_stock_type, $actual_stock_type);
    }

    public function testGetStockType(): void {
        $stock = Stock::getStockBySymbol('SQIA3');
        $actual_stock_type = $stock->getStockType();
        $expected_stock_type = StockType::getStockTypeByType(StockType::ACAO_TYPE);

        $this->assertEquals($expected_stock_type, $actual_stock_type);
    }

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
        $name = $stock->getStockName();

        $this->assertNull($name);
        $this->assertNull($stock->name);
    }

    public function testLoadStockName_ShouldLoadNameFromAlphaVantageAPI(): void {
        $stock = new Stock();
        $stock->store('ITSA4');
        $name = $stock->getStockName();

        $this->assertEquals('Itausa - Investimentos Itau S.A.', $name);
        $this->assertEquals('Itausa - Investimentos Itau S.A.', $stock->name);
    }
}
