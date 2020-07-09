<?php

namespace Tests\Portfolio\API;

use App\Portfolio\API\AlphaVantageAPI;
use Tests\TestCase;

class AlphaVantageAPITest extends TestCase {

    public function testGetSymbolName_InvalidSymbol_ShouldReturnNull(): void {
        $name = AlphaVantageAPI::getStockNameForSymbol('x1faf3');

        $this->assertNull($name);
    }

    public function testGetSymbolName(): void {
        $name = AlphaVantageAPI::getStockNameForSymbol('XPML11');

        $this->assertEquals('Xp Malls Fundo Investimentos Imobiliarios', $name);
    }
}
