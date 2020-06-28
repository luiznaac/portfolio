<?php

namespace App\Http\Controllers;

use App\Model\Stock\Stock;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StocksController extends Controller
{

    public function index(): Response
    {
    }

    public function create(): Response
    {
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'symbol' => 'required',
        ]);

        $stock = new Stock();
        $stock->symbol = $request->input('symbol');
        $stock->save();

        return redirect('/stocks')->with('success', "$stock->symbol Registered");
    }

    public function show(int $id): Response
    {
    }

    public function edit(int $id): Response
    {
    }

    public function update(Request $request, int $id): Response
    {
    }

    public function destroy(int $id): Response
    {
    }
}
