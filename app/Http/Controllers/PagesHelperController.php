<?php

namespace App\Http\Controllers;

use App\Portfolio\Utils\PagesHelper;
use Illuminate\Http\Request;

class PagesHelperController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }

    public static function getConsolidationState(): int {
        return PagesHelper::updateAndGetConsolidationState();
    }
}
