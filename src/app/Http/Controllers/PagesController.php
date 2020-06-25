<?php

namespace App\Http\Controllers;

use App\Model\Stock\Stock;
use Illuminate\View\View;

class PagesController extends Controller
{

    public function index(): View {
        return view('pages.index');
    }

    public function stocks(): View {
        $data = [
            'stocks' => Stock::all(),
        ];

        return view('pages.stocks')
            ->with($data);
    }
}
