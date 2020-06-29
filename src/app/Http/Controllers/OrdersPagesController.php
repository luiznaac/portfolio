<?php

namespace App\Http\Controllers;

use App\Model\Order\Order;
use Illuminate\View\View;

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
}
