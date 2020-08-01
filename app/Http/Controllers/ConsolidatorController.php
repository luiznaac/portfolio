<?php

namespace App\Http\Controllers;

use App\Portfolio\Consolidator\ConsolidatorStateMachine;

class ConsolidatorController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function force() {
        try {
            $this->startConsolidateAndWaitForConsolidatingState();

            $status = 'ok';
            $message = "Your portfolio is being consolidated!";
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/')->with($status, $message);
    }

    public function consolidate() {
        try {
            $this->startConsolidateAndWaitForConsolidatingState();

            $status = 'ok';
            $message = "Your portfolio is being consolidated!";
        } catch (\Exception $exception) {
            $status = 'error';
            $message = $exception->getMessage();
        }

        return redirect('/')->with($status, $message);
    }

    private function startConsolidateAndWaitForConsolidatingState() {
        $user_id = auth()->id();
        exec("php ../artisan consolidate $user_id > /dev/null &");
        $this->waitForConsolidatingState();
    }

    private function waitForConsolidatingState() {
        do{
            usleep(100000);
            $state = ConsolidatorStateMachine::getConsolidatorStateMachine()->state;
        }while($state != 2);
    }
}
