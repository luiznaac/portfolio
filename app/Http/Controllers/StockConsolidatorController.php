<?php

namespace App\Http\Controllers;

use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\StockConsolidator;
use Illuminate\Http\Request;

class StockConsolidatorController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }
}
