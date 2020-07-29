<?php

namespace App\Http\Controllers;

use App\Model\Stock\Stock;
use App\Model\Stock\Dividend\StockDividend;
use App\Portfolio\Utils\PagesHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ConsolidatorController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function force() {
        try {
            PagesHelper::update();

            $status = 'ok';
            $message = "Everything was updated!";
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/')->with($status, $message);
    }
}
