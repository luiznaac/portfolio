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
            ['stock_id' => 3, 'date' => '2019-09-18', 'price' => 112],
            ['stock_id' => 3, 'date' => '2019-09-19', 'price' => 112.46],
            ['stock_id' => 3, 'date' => '2019-09-20', 'price' => 111.85],
            ['stock_id' => 3, 'date' => '2019-09-23', 'price' => 111.29],
            ['stock_id' => 3, 'date' => '2019-09-24', 'price' => 110.89],
            ['stock_id' => 3, 'date' => '2019-09-25', 'price' => 110.61],
            ['stock_id' => 3, 'date' => '2019-09-26', 'price' => 110.89],
            ['stock_id' => 3, 'date' => '2019-09-27', 'price' => 111.78],
            ['stock_id' => 3, 'date' => '2019-09-30', 'price' => 113.5],
            ['stock_id' => 3, 'date' => '2019-10-01', 'price' => 113.47],
            ['stock_id' => 3, 'date' => '2019-10-02', 'price' => 113.05],
            ['stock_id' => 3, 'date' => '2019-10-03', 'price' => 113],
            ['stock_id' => 3, 'date' => '2019-10-04', 'price' => 113],
            ['stock_id' => 3, 'date' => '2019-10-07', 'price' => 114.2],
            ['stock_id' => 3, 'date' => '2019-10-08', 'price' => 114],
            ['stock_id' => 3, 'date' => '2019-10-09', 'price' => 114],
            ['stock_id' => 3, 'date' => '2019-10-10', 'price' => 113.9],
            ['stock_id' => 3, 'date' => '2019-10-11', 'price' => 114],
            ['stock_id' => 3, 'date' => '2019-10-14', 'price' => 113.8],
            ['stock_id' => 3, 'date' => '2019-10-15', 'price' => 113.93],
            ['stock_id' => 3, 'date' => '2019-10-16', 'price' => 113.86],
            ['stock_id' => 3, 'date' => '2019-10-17', 'price' => 113],
            ['stock_id' => 3, 'date' => '2019-10-18', 'price' => 114.13],
            ['stock_id' => 3, 'date' => '2019-10-21', 'price' => 117.45],
            ['stock_id' => 3, 'date' => '2019-10-22', 'price' => 118.25],
            ['stock_id' => 3, 'date' => '2019-10-23', 'price' => 120],
            ['stock_id' => 3, 'date' => '2019-10-24', 'price' => 118.99],
            ['stock_id' => 3, 'date' => '2019-10-25', 'price' => 118.2],
        ]);

        DB::table('stock_dividends')->insert([
            ['stock_id' => 3, 'type' => 'Dividendo', 'date_paid' => '2019-09-25', 'reference_date' => '2019-09-18', 'value' => 0.57],
            ['stock_id' => 3, 'type' => 'Dividendo', 'date_paid' => '2019-10-25', 'reference_date' => '2019-10-18', 'value' => 0.59],
        ]);

        DB::table('bond_types')->insert([
            ['id' => 1, 'type' => 'Tesouro Direto', 'description' => 'Tesouro Direto'],
            ['id' => 2, 'type' => 'CDB', 'description' => 'Certificado de Depósito Bancário'],
            ['id' => 3, 'type' => 'LC', 'description' => 'Letra de Câmbio'],
            ['id' => 4, 'type' => 'LCI', 'description' => 'Letra de Crédito Imobiliário'],
            ['id' => 5, 'type' => 'LCA', 'description' => 'Letra de Crédito do Agronegócio'],
            ['id' => 6, 'type' => 'CRI', 'description' => 'Certificado de Recebíveis Imobiliários'],
            ['id' => 7, 'type' => 'CRA', 'description' => 'Certificado de Recebíveis do Agronegócio'],
        ]);

        DB::table('indices')->insert([
            ['id' => 1, 'index' => 'Selic', 'description' => 'Sistema Especial de Liquidação e Custódia'],
            ['id' => 2, 'index' => 'CDI', 'description' => 'Certificado de Depósito Interbancário'],
            ['id' => 3, 'index' => 'IPCA', 'description' => 'Índice Nacional de Preços ao Consumidor Amplo'],
        ]);

        DB::table('index_values')->insert([
            ['index_id' => 1, 'date' => '2020-07-06', 'value' => 0.008442],
            ['index_id' => 1, 'date' => '2020-07-07', 'value' => 0.008442],
            ['index_id' => 1, 'date' => '2020-07-08', 'value' => 0.008442],
            ['index_id' => 1, 'date' => '2020-07-09', 'value' => 0.008442],
            ['index_id' => 1, 'date' => '2020-07-10', 'value' => 0.008442],

            ['index_id' => 2, 'date' => '2020-07-06', 'value' => 0.008442],
            ['index_id' => 2, 'date' => '2020-07-07', 'value' => 0.008442],
            ['index_id' => 2, 'date' => '2020-07-08', 'value' => 0.008442],
            ['index_id' => 2, 'date' => '2020-07-09', 'value' => 0.008442],
            ['index_id' => 2, 'date' => '2020-07-10', 'value' => 0.008442],

            ['index_id' => 3, 'date' => '2020-07-01', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-02', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-03', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-06', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-07', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-08', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-09', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-10', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-13', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-14', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-15', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-16', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-17', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-20', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-21', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-22', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-23', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-24', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-27', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-28', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-29', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-30', 'value' => 0.008372],
            ['index_id' => 3, 'date' => '2020-07-31', 'value' => 0.008372],
        ]);
    }
}
