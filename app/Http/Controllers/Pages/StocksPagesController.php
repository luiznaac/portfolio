<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Stock\Stock;
use App\Model\Stock\StockType;
use Illuminate\View\View;

class StocksPagesController extends Controller
{
    const DEFAULT_DIR = 'pages.stocks';

    public function __construct() {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    public function index(): View {
        $data = [
            'stocks' => Stock::query()->orderBy('symbol')->get(),
            'stock_types' => StockType::getStockTypesFromCache(),
        ];

        return view(self::DEFAULT_DIR . ".index")
            ->with($data);
    }

    public function create(): View {
        return view(self::DEFAULT_DIR . ".create");
    }

    public function show(int $id) {
        /** @var Stock $stock */
        $stock = Stock::find($id);

        $data = [
            'stock' => $stock,
            'stock_prices' => $stock->getStockPrices(),
        ];

        return view(self::DEFAULT_DIR . ".show")
            ->with($data);
    }
}
