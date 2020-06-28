<?php

namespace Tests\Model;

use App\Model\Stock\Stock;
use Tests\TestCase;

class StockTest extends TestCase {

    public function testStoreStock_StockNotFound_ShouldStoreNull(): void {
        $stock = new Stock();
        $stock->store('XXXXXX');

        $this->assertNull($stock->name);
    }

    public function testStoreStock_ShouldLoadNameFromAlphaVantageAPI(): void {
        $stock = new Stock();
        $stock->store('ITSA4');

        $this->assertEquals('Itausa - Investimentos Itau S.A.', $stock->name);
    }
}
