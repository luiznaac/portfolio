<?php

namespace App\Http\Controllers;

use App\Model\Stock\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StocksController extends Controller
{

    public function store(Request $request)
    {
        $this->validate($request,[
            'symbol' => 'required',
        ]);

        $stock = new Stock();
        $stock->store($request->input('symbol'));

        return redirect('/stocks')->with('success', "$stock->symbol Registered");
    }

   public function loadInfoForDate(Request $request)
   {
       $this->validate($request,[
           'date' => 'required|date',
           'stock_id' => 'required',
       ]);

       $date = Carbon::createFromFormat('Y-m-d', $request->input('date'));
       /** @var Stock $stock */
       $stock = Stock::find($request->input('stock_id'));
       $stock->loadStockInfoForDate($date);

       return redirect("/stocks/$stock->id")->with('success', $date->toDateString() . " price for $stock->symbol was loaded.");
   }
}
