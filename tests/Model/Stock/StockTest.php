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

    public function testLoadStockName_ShouldLoadNameFromAlphaVantageAPI(): void {
        $stock = Stock::getStockBySymbol('ITSA4');
        $name = $stock->getStockName();

        $this->assertEquals('Itausa - Investimentos Itau S.A.', $name);
        $this->assertEquals('Itausa - Investimentos Itau S.A.', $stock->name);
    }
}
