<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Stock\Stock;
use App\Model\Stock\StockPrice;
use App\Model\Stock\StockType;
use Illuminate\View\View;

class StocksPagesController extends Controller
{
    const DEFAULT_DIR = 'pages.stocks';

    public function __construct() {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    public function index(): View {
        $stocks = Stock::query()->orderBy('symbol')->get();
        foreach ($stocks as $stock) {
            $stock->last_price = StockPrice::query()
                ->where('stock_id', $stock->id)
                ->orderByDesc('date')
                ->first()->price;
        }

        $data = [
            'stocks' => $stocks,
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
            'stock_type' => $stock->getStockType(),
            'stock_prices' => $stock->getStockPrices(),
            'stock_dividends' => $stock->getStockDividends(),
        ];

        return view(self::DEFAULT_DIR . ".show")
            ->with($data);
    }
}
