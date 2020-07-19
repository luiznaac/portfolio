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
            'amount_updated' => $dashboard_data['amount_updated'],
            'amount_contributed' => $dashboard_data['amount_contributed'],
            'overall_variation' => $dashboard_data['overall_variation'],
            'dividends_amount' => $dashboard_data['dividends_amount'],
        ];

        return view(self::DEFAULT_DIR . ".index")->with($data);
    }
}
