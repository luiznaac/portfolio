<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Order\Order;
use Illuminate\View\View;

class OrdersPagesController extends Controller
{
    const DEFAULT_DIR = 'pages.orders';

    public function __construct() {
        $this->middleware('auth');
    }

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
}
