<?php

namespace App\Http\Controllers;

use App\Model\Order\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

class OrdersPagesController extends Controller
{
    const DEFAULT_DIR = 'pages.orders';

    public function index(): View {
        $data = [
            'orders' => Order::all(),
        ];

        return view(self::DEFAULT_DIR . ".index")
            ->with($data);
    }

    public function create(): View {
        return view(self::DEFAULT_DIR . ".create");
    }

    public function apiRouteStore(Request $request) {
        $controller = new OrdersController();
        /** @var JsonResponse $response */
        $response = $controller->store($request);
        $response = json_decode($response->getContent());

        return redirect('/orders')->with($response->status, $response->message);
    }
}
