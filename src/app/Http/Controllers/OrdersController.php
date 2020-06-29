<?php

namespace App\Http\Controllers;

use App\Model\Order\Order;
use App\Model\Stock\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class OrdersController extends Controller
{

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
            $stock = Stock::firstOrCreate(['symbol' => $request->input('symbol')]);
            $date = Carbon::createFromFormat('Y-m-d', $request->input('date'));

            $order = new Order();
            $order->store(
                $stock,
                $date,
                $request->input('type'),
                $request->input('quantity'),
                $request->input('price'),
                $request->input('cost')
            );

            return new JsonResponse(['status' => 'ok', 'message' => "$order->sequence Registered"], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['status' => 'error', 'message' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
