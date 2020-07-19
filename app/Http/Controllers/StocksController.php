<?php

namespace App\Http\Controllers;

use App\Model\Stock\Stock;
use App\Model\Stock\Dividend\StockDividend;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StocksController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function updateInfos() {
        try {
            Stock::updateInfosForAllStocks();
            StockDividend::loadHistoricDividendsForAllStocks();

            $status = 'ok';
            $message = "Infos updated";
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/stocks')->with($status, $message);
    }

    public function store(Request $request) {
        $this->validate($request,[
            'symbol' => 'required',
        ]);

        try {
            $stock = new Stock();
            $stock->store($request->input('symbol'));

            $status = 'ok';
            $message = "$stock->symbol Registered";
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/stocks')->with($status, $message);
    }

    public function loadInfoForDate(Request $request) {
        $this->validate($request,[
            'date' => 'required|date',
            'stock_id' => 'required',
        ]);

        try {
            $date = Carbon::createFromFormat('Y-m-d', $request->input('date'));
            /** @var Stock $stock */
            $stock = Stock::find($request->input('stock_id'));
            $stock->getStockPriceForDate($date);

            $status = 'ok';
            $message = $date->toDateString() . " price for $stock->symbol was loaded.";
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/stocks/' . $request->input('stock_id'))->with($status, $message);
    }
}
