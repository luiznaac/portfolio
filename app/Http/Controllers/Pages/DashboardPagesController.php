<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Bond\Bond;
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
            'stock_types' => StockType::getStockTypesFromCache(),
            'bonds' => Bond::getAllBondsFromCache(),
            'contributed_amount' => $dashboard_data['contributed_amount'],
            'updated_amount' => $dashboard_data['updated_amount'],
            'dividends_amount' => $dashboard_data['dividends_amount'],
            'overall_variation' => $dashboard_data['overall_variation'],
            'stock_allocation' => $dashboard_data['stock_allocation'],
            'bond_allocation' => $dashboard_data['bond_allocation'],
            'stock_type_allocations' => $dashboard_data['stock_type_allocations'],
            'stock_allocations' => $dashboard_data['stock_allocations'],
            'bond_allocations' => $dashboard_data['bond_allocations'],
            'stock_positions_list' => $dashboard_data['stock_positions_list'],
            'bond_positions_list' => $dashboard_data['bond_positions_list'],
        ];

        return view(self::DEFAULT_DIR . ".index")->with($data);
    }
}
