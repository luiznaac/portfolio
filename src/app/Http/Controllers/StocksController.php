<?php

namespace App\Http\Controllers;

use App\Model\Stock\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class StocksController extends Controller
{

    public function store(Request $request)
    {
        $this->validate($request,[
            'symbol' => 'required',
        ]);

        $stock = new Stock();
        $stock->store($request->input('symbol'));

        return new JsonResponse(['status' => 'ok', 'message' => "$stock->symbol Registered"], Response::HTTP_OK);
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

       return new JsonResponse(['status' => 'ok', 'message' => $date->toDateString() . " price for $stock->symbol was loaded."], Response::HTTP_OK);
   }
}
