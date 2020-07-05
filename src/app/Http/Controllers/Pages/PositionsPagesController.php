<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Order\Order;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use Illuminate\View\View;

class PositionsPagesController extends Controller
{
    const DEFAULT_DIR = 'pages.positions';
    const STOCKS_SUBDIR = '.stocks';

    public function showStocks() {
        $stocks = Order::getAllStocksWithOrders();

        $data = [
            'stocks' => $stocks,
        ];

        return view(self::DEFAULT_DIR . self::STOCKS_SUBDIR . ".positions")
            ->with($data);
    }

    public function showStockDetailedPosition(int $id) {
        /** @var Stock $stock */
        $stock = Stock::find($id);

        $data = [
            'stock' => $stock,
            'stock_positions' => StockPosition::getPositionsForStock($stock),
        ];

        return view(self::DEFAULT_DIR . self::STOCKS_SUBDIR . ".detailed")
            ->with($data);
    }
}