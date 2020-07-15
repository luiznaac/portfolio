<?php

namespace Tests\Portfolio\API\Abstracts;

use App\Model\Stock\StockType;
use Carbon\Carbon;
use Tests\TestCase;

abstract class HolidayAPITest extends TestCase {

    public function dataProviderForTestGetHolidaysForYear(): array {
        return [
            '2018' => [
                'year' => '2018',
                'expected_holidays' => [
                    '2018-01-01' => 'Confraternização Universal',
                    '2018-01-25' => 'Aniversário de São Paulo',
                    '2018-02-12' => 'Carnaval',
                    '2018-02-13' => 'Carnaval',
                    '2018-03-30' => 'Paixão de Cristo',
                    '2018-05-01' => 'Dia do Trabalho',
                    '2018-05-31' => 'Corpus Christi',
                    '2018-07-09' => 'Revolução Constitucionalista',
                    '2018-09-07' => 'Independência do Brasil',
                    '2018-10-12' => 'Nossa Senhora Aparecida',
                    '2018-11-02' => 'Finados',
                    '2018-11-15' => 'Proclamação da República',
                    '2018-11-20' => 'Consciência Negra',
                    '2018-12-24' => 'Véspera de Natal',
                    '2018-12-25' => 'Natal',
                    '2018-12-31' => 'Véspera de Ano Novo',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetHolidaysForYear
     */
    abstract public function testGetGetHolidaysForYear(string $year, array $expected_holidays): void;
}
