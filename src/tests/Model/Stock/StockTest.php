<?php

namespace Tests\Model\Stock;

use App\Model\Stock\Stock;
use App\Model\Stock\StockInfo;
use Carbon\Carbon;
use Tests\TestCase;

class StockTest extends TestCase {

    public function testLoadStockInfoForDate_ShouldLoadAndStore(): void {
        $stock = new Stock();
        $stock->store('BOVA11');

        $date = Carbon::createFromFormat('Y-m-d', '2020-06-26');
        $stock->loadStockInfoForDate($date);

        /** @var StockInfo $stock_info */
        $stock_info = $stock->getStockInfos()->first();

        $this->assertEquals($date->toDateString(), $stock_info->date);
        $this->assertEquals(90.22, $stock_info->price);
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
