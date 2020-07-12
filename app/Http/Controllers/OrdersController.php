<?php

namespace App\Http\Controllers;

use App\Model\Order\Order;
use App\Model\Stock\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrdersController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'symbol' => 'required',
            'date' => 'required|date',
            'type' => 'required',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'cost' => 'required|numeric',
        ]);

        try {
            $symbol = $request->input('symbol');

            if(!Stock::isValidSymbol($symbol)) {
                $status = 'error';
                $message = "$symbol is not a valid stock.";
                return back()->with($status, $message);
            }

            $date = Carbon::createFromFormat('Y-m-d', $request->input('date'));

            $order = Order::createOrder(
                $symbol,
                $date,
                $request->input('type'),
                $request->input('quantity'),
                $request->input('price'),
                $request->input('cost')
            );

            $stock = Stock::find($order->stock_id);

            $status = 'ok';
            $message = "$order->sequence for $stock->symbol registered";
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/orders')->with($status, $message);
    }

    public function delete(Request $request) {
        $this->validate($request,[
            'id' => 'required',
        ]);

        try {
            /** @var Order $order */
            $order = Order::getBaseQuery()
                ->where('id', $request->input('id'));
            $order->delete();

            $status = 'ok';
            $message = 'Order deleted';
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/orders')->with($status, $message);
    }
}
