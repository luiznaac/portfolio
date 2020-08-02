<?php

namespace App\Http\Controllers;

use App\Model\Bond\Bond;
use App\Model\Bond\BondIssuer;
use App\Model\Bond\BondType;
use App\Model\Index\Index;
use Illuminate\Http\Request;

class BondsController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function store(Request $request) {
        $this->validate($request,[
            'bond_issuer_id' => 'required',
            'bond_type_id' => 'required',
            'days' => 'required|integer|gt:30',
        ]);

        try {
            $index_id = $request->input('index_id');
            $index_rate = $request->input('index_rate');
            $interest_rate = $request->input('interest_rate');

            if ($index_id == 0 && (!$interest_rate || $interest_rate <= 0)) {
                $status = 'error';
                $message = 'Index or extra interest rate was not set. You have to set at least one';
                return back()->with($status, $message);
            }

            if ($index_id <> 0 && $index_rate <= 0) {
                $status = 'error';
                $message = 'Rate must be greater than zero';
                return back()->with($status, $message);
            }

            $bond_issuer = BondIssuer::find($request->input('bond_issuer_id'));
            $bond_type = BondType::find($request->input('bond_type_id'));
            $index = Index::find($index_id);
            $index_rate = $request->input('index_rate');
            $interest_rate = $request->input('interest_rate');
            $days = $request->input('days');

            Bond::store(
                $bond_issuer,
                $bond_type,
                $index,
                $index_rate,
                $interest_rate,
                $days
            );

            $status = 'ok';
            $message = "Bond Created";
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/bonds')->with($status, $message);
    }
}
