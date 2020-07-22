<?php

namespace App\Http\Controllers;

use App\Model\Bond\Bond;
use App\Model\Bond\BondOrder;
use App\Portfolio\Utils\Calendar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BondOrdersController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'bond_id' => 'required',
            'date' => 'required|date',
            'type' => 'required',
            'amount' => 'required|numeric',
        ]);

        try {
            $bond_id = $request->input('bond_id');

            if($bond_id == 0) {
                $status = 'error';
                $message = "You must select a bond.";
                return back()->with($status, $message);
            }

            $date = Carbon::createFromFormat('Y-m-d', $request->input('date'));

            if(!Calendar::isWorkingDay($date)) {
                $status = 'error';
                $message = $date->format('d/m/Y') . ' is not a weekday.';
                return back()->with($status, $message);
            }

            BondOrder::createOrder(
                Bond::find($bond_id),
                $date,
                $request->input('type'),
                $request->input('amount')
            );

            $status = 'ok';
            $message = "Bond order registered";
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/bonds/orders')->with($status, $message);
    }

    public function delete(Request $request) {
        $this->validate($request,[
            'id' => 'required',
        ]);

        try {
            /** @var BondOrder $order */
            $order = BondOrder::getBaseQuery()
                ->where('id', $request->input('id'))
                ->get()->first();
            $order->delete();

            $status = 'ok';
            $message = 'Order deleted';
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/bonds/orders')->with($status, $message);
    }
}
