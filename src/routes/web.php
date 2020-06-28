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
Route::get('/stocks', 'StocksPagesController@index');
Route::get('/stocks/create', 'StocksPagesController@create');

Route::post('/store-stock', ['uses' => 'StocksController@store']);
