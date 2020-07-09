<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PagesController extends Controller
{

    public function index(): View {
        return view('pages.index');
    }
}
