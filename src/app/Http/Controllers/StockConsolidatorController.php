<?php

namespace App\Http\Controllers;

use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\StockConsolidator;
use Illuminate\Http\Request;

class StockConsolidatorController extends Controller {

    public function consolidateForStock(Request $request) {
        $this->validate($request,[
            'stock_id' => 'required',
        ]);

        try {
            $stock = Stock::find($request->input('stock_id'));
            StockConsolidator::consolidateFromBegin($stock);

            $status = 'ok';
            $message = "Positions for $stock->symbol consolidated.";
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/positions/stocks/' . $request->input('stock_id'))->with($status, $message);
    }
}
