<?php

namespace App\Http\Controllers;

use App\Portfolio\Utils\PagesHelper;
use Illuminate\Http\Request;

class PagesHelperController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }

    public function update(Request $request) {
        try {
            PagesHelper::update();

            $status = 'ok';
            $message = 'Everything was updated!';
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return back()->with($status, $message);
    }

    public static function shouldShowUpdateButton(): bool {
        return PagesHelper::shouldUpdatePositions();
    }
}
