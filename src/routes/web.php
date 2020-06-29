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

Route::get('/', 'PagesController@index');

// STOCKS

Route::get('/stocks', 'StocksPagesController@index');
Route::get('/stocks/create', 'StocksPagesController@create');
Route::get('/stocks/{id}', 'StocksPagesController@show');

Route::post('/stocks/store', 'StocksPagesController@apiRouteStore');
Route::post('/stocks/load-info-for-date', 'StocksPagesController@apiRouteLoadInfoForDate');

// ORDERS

Route::get('/orders', 'OrdersPagesController@index');
Route::get('/orders/create', 'OrdersPagesController@create');

Route::post('/orders/store', 'OrdersPagesController@apiRouteStore');
