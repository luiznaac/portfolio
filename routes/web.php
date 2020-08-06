<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// DASHBOARD

Route::get('/', 'Pages\DashboardPagesController@index');

Route::post('/consolidate', 'ConsolidatorController@consolidate');

// STOCKS

Route::get('/stocks', 'Pages\StocksPagesController@index');
Route::get('/stocks/create', 'Pages\StocksPagesController@create');
Route::get('/stocks/show/{id}', 'Pages\StocksPagesController@show');

Route::post('/stocks/update_infos', 'StocksController@updateInfos');

// ORDERS

Route::get('/stocks/orders', 'Pages\OrdersPagesController@index');
Route::get('/stocks/orders/create', 'Pages\OrdersPagesController@create');

Route::post('/stocks/orders/store', 'OrdersController@store');
Route::post('/stocks/orders/delete', 'OrdersController@delete');

Route::get('/bonds/orders', 'Pages\BondOrdersPagesController@index');
Route::get('/bonds/orders/create', 'Pages\BondOrdersPagesController@create');

Route::post('/bonds/orders/store', 'BondOrdersController@store');
Route::post('/bonds/orders/store-treasury', 'BondOrdersController@storeTreasury');
Route::post('/bonds/orders/delete', 'BondOrdersController@delete');
Route::post('/bonds/orders/delete-treasury', 'BondOrdersController@deleteTreasury');

// POSITIONS

Route::get('/positions/stocks', 'Pages\PositionsPagesController@showStocks');
Route::get('/positions/stocks/{id}', 'Pages\PositionsPagesController@showStockDetailedPosition');

Route::get('/positions/bonds', 'Pages\PositionsPagesController@showBonds');
Route::get('/positions/bonds/{id}', 'Pages\PositionsPagesController@showBondOrderDetailedPosition');

// BONDS

Route::get('/bonds', 'Pages\BondsPagesController@index');
Route::get('/bonds/create', 'Pages\BondsPagesController@create');
Route::get('/bonds/issuers', 'Pages\BondIssuersPagesController@index');
Route::get('/bonds/issuers/create', 'Pages\BondIssuersPagesController@create');

Route::post('/bonds/store', 'BondsController@store');
Route::post('/bonds/store-treasury', 'BondsController@storeTreasury');
Route::post('/bonds/issuers/store', 'BondIssuersController@store');

// CONSOLIDATOR

Route::get('/force-consolidation', 'ConsolidatorController@force');

Auth::routes();
