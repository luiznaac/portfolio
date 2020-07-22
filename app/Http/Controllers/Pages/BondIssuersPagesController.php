<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Bond\BondIssuer;
use Illuminate\View\View;

class BondIssuersPagesController extends Controller
{
    const DEFAULT_DIR = 'pages.bonds.issuers';

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(): View {
        $data = [
            'bond_issuers' => BondIssuer::query()->orderBy('name')->get(),
        ];

        return view(self::DEFAULT_DIR . ".index")
            ->with($data);
    }

    public function create(): View {
        return view(self::DEFAULT_DIR . ".create");
    }
}
