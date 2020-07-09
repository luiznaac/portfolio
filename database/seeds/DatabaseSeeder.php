<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('stock_types')->insert([
            ['id' => 1, 'type' => 'Ação', 'description' => 'Ação'],
            ['id' => 2, 'type' => 'ETF',  'description' => 'Exchange-Traded Fund'],
            ['id' => 3, 'type' => 'FII',  'description' => 'Fundo de Investimento Imobiliário'],
        ]);

        DB::table('stocks')->insert([
            ['id' => 1, 'symbol' => 'BOVA11', 'stock_type_id' => 2, 'name' => 'iShares Ibovespa Fundo de Índice'],
            ['id' => 2, 'symbol' => 'SQIA3',  'stock_type_id' => 1, 'name' => 'Sinqia SA'],
            ['id' => 3, 'symbol' => 'XPML11', 'stock_type_id' => 3, 'name' => 'XP Malls FII'],
        ]);

        DB::table('stock_prices')->insert([
            ['stock_id' => 1, 'date' => '2020-06-22', 'price' => 91.75],
            ['stock_id' => 1, 'date' => '2020-06-23', 'price' => 92.26],
            ['stock_id' => 1, 'date' => '2020-06-24', 'price' => 90.77],
            ['stock_id' => 1, 'date' => '2020-06-25', 'price' => 92.39],
            ['stock_id' => 1, 'date' => '2020-06-26', 'price' => 90.22],
            ['stock_id' => 1, 'date' => '2020-06-29', 'price' => 92.3],
            ['stock_id' => 1, 'date' => '2020-06-30', 'price' => 91.62],
            ['stock_id' => 1, 'date' => '2020-07-01', 'price' => 92.68],
            ['stock_id' => 1, 'date' => '2020-07-02', 'price' => 92.5],
            ['stock_id' => 1, 'date' => '2020-07-03', 'price' => 93.19],

            ['stock_id' => 2, 'date' => '2020-06-22', 'price' => 19.36],
            ['stock_id' => 2, 'date' => '2020-06-23', 'price' => 19.5],
            ['stock_id' => 2, 'date' => '2020-06-24', 'price' => 18.77],
            ['stock_id' => 2, 'date' => '2020-06-25', 'price' => 18.98],
            ['stock_id' => 2, 'date' => '2020-06-26', 'price' => 18.51],
            ['stock_id' => 2, 'date' => '2020-06-29', 'price' => 18.72],
            ['stock_id' => 2, 'date' => '2020-06-30', 'price' => 19.24],
            ['stock_id' => 2, 'date' => '2020-07-01', 'price' => 21.71],
            ['stock_id' => 2, 'date' => '2020-07-02', 'price' => 22.1],
            ['stock_id' => 2, 'date' => '2020-07-03', 'price' => 23.36],

            ['stock_id' => 3, 'date' => '2020-06-22', 'price' => 105.00],
            ['stock_id' => 3, 'date' => '2020-06-23', 'price' => 105.00],
            ['stock_id' => 3, 'date' => '2020-06-24', 'price' => 104.40],
            ['stock_id' => 3, 'date' => '2020-06-25', 'price' => 103.68],
            ['stock_id' => 3, 'date' => '2020-06-26', 'price' => 103.25],
            ['stock_id' => 3, 'date' => '2020-06-29', 'price' => 102.50],
            ['stock_id' => 3, 'date' => '2020-06-30', 'price' => 103.90],
            ['stock_id' => 3, 'date' => '2020-07-01', 'price' => 103.00],
            ['stock_id' => 3, 'date' => '2020-07-02', 'price' => 103.00],
            ['stock_id' => 3, 'date' => '2020-07-03', 'price' => 104.00],
        ]);
    }
}
