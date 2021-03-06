<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Bond\Bond;
use App\Model\Bond\BondOrder;
use App\Model\Bond\BondPosition;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Model\Stock\StockPrice;

class PositionsPagesController extends Controller
{
    const DEFAULT_DIR = 'pages.positions';
    const STOCKS_SUBDIR = '.stocks';
    const BONDS_SUBDIR = '.bonds';

    public function __construct() {
        $this->middleware('auth');
    }

    public function showStocks() {
        $stock_positions = StockPosition::getLastStockPositions();

        $stocks = [];
        foreach ($stock_positions as $stock_position) {
            $stocks[$stock_position->stock_id] = Stock::find($stock_position->stock_id);
            $stocks[$stock_position->stock_id]->last_price = StockPrice::query()
                ->where('stock_id', $stock_position->stock_id)
                ->orderByDesc('date')
                ->first()->price;
        }

        $data = [
            'stocks' => $stocks,
            'stock_positions' => $stock_positions,
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

    public function showBonds() {
        $bond_positions = BondPosition::getLastBondPositions();

        $bonds = [];
        foreach ($bond_positions as &$bond_position) {
            $bond_order = BondOrder::find($bond_position->bond_order_id);
            $bonds[$bond_order->bond_id] = Bond::find($bond_order->bond_id);
            $bond_position['bond_id'] = $bond_order->bond_id;
        }

        $data = [
            'bonds' => $bonds,
            'bond_positions' => $bond_positions,
        ];

        return view(self::DEFAULT_DIR . self::BONDS_SUBDIR . ".positions")
            ->with($data);
    }

    public function showBondOrderDetailedPosition(int $id) {
        /** @var BondOrder $bond_order */
        $bond_order = BondOrder::getBaseQuery()
            ->where('id', $id)->first();

        if(!$bond_order) {
            return redirect('/')
                ->with('error', 'Not available.');
        }

        /** @var Bond $bond */
        $bond = Bond::find($bond_order->bond_id);

        $data = [
            'bond' => $bond,
            'bond_positions' => BondPosition::getPositionsForBondOrder($bond_order),
        ];

        return view(self::DEFAULT_DIR . self::BONDS_SUBDIR . ".detailed")
            ->with($data);
    }
}
