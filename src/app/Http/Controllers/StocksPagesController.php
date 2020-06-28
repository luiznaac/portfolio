<?php

namespace App\Http\Controllers;

use App\Model\Stock\Stock;
use Illuminate\View\View;

class StocksPagesController extends Controller
{
    const DEFAULT_DIR = 'pages.stocks';

    public function index(): View {
        $data = [
            'stocks' => Stock::all(),
        ];

        return view(self::DEFAULT_DIR . ".index")
            ->with($data);
    }

    public function create(): View {
        return view(self::DEFAULT_DIR . ".create");
    }
}
