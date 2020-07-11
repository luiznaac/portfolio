<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Stock\Stock;
use App\Model\Stock\StockType;
use App\Portfolio\Dashboard\Dashboard;

class DashboardPagesController extends Controller
{
    const DEFAULT_DIR = 'pages.dashboard';

    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
        $dashboard_data = Dashboard::getData();

        $data = [
            'stock_positions_by_type' => $dashboard_data['stock_positions_by_type'],
            'stock_types' => StockType::getStockTypesFromCache(),
            'stocks' => Stock::getAllStocksFromCache(),
        ];

        return view(self::DEFAULT_DIR . ".index")->with($data);
    }
}
