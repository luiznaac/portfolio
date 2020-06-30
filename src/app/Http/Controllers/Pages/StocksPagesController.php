<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Stock\Stock;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

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

    public function show(int $id) {
        /** @var Stock $stock */
        $stock = Stock::find($id);

        $data = [
            'stock' => $stock,
            'stock_infos' => $stock->getStockInfos(),
        ];

        return view(self::DEFAULT_DIR . ".show")
            ->with($data);
    }

    public function loadInfoForDate(Request $request) {
        $controller = new StocksController();
        /** @var JsonResponse $response */
        $response = $controller->loadInfoForDate($request);
        $response = json_decode($response->getContent());

        return redirect('/stocks/' . $request->input('stock_id'))->with($response->status, $response->message);
    }
}
