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
}
