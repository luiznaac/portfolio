<?php

namespace App\Http\Controllers;

use App\Model\Bond\BondIssuer;
use Illuminate\Http\Request;

class BondIssuersController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function store(Request $request) {
        $this->validate($request,[
            'name' => 'required',
        ]);

        try {
            $bond_issuer = new BondIssuer();
            $bond_issuer->name = $request->input('name');
            $bond_issuer->save();

            $status = 'ok';
            $message = "$bond_issuer->name Created";
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/bonds/issuers')->with($status, $message);
    }
}
