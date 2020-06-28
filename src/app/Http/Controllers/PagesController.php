<?php

namespace App\Http\Controllers;

use App\Model\Stock\Stock;
use Illuminate\View\View;

class PagesController extends Controller
{

    public function index(): View {
        return view('pages.index');
    }
}