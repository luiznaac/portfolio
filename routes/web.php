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

Route::post('/update', 'PagesHelperController@update');

// STOCKS

Route::get('/stocks', 'Pages\StocksPagesController@index');
Route::get('/stocks/create', 'Pages\StocksPagesController@create');
Route::get('/stocks/{id}', 'Pages\StocksPagesController@show');

Route::post('/stocks/store', 'StocksController@store');
Route::post('/stocks/update_infos', 'StocksController@updateInfos');
Route::post('/stocks/load-info-for-date', 'StocksController@loadInfoForDate');

// ORDERS

Route::get('/orders', 'Pages\OrdersPagesController@index');
Route::get('/orders/create', 'Pages\OrdersPagesController@create');

Route::post('/orders/store', 'OrdersController@store');
Route::post('/orders/delete', 'OrdersController@delete');

// POSITIONS

Route::get('/positions/stocks', 'Pages\PositionsPagesController@showStocks');
Route::get('/positions/stocks/{id}', 'Pages\PositionsPagesController@showStockDetailedPosition');

// BONDS

Route::get('/bonds', 'Pages\BondsPagesController@index');
Route::get('/bonds/create', 'Pages\BondsPagesController@create');
Route::get('/bonds/issuers', 'Pages\BondIssuersPagesController@index');
Route::get('/bonds/issuers/create', 'Pages\BondIssuersPagesController@create');

Route::post('/bonds/store', 'BondsController@store');
Route::post('/bonds/issuers/store', 'BondIssuersController@store');

Auth::routes();
