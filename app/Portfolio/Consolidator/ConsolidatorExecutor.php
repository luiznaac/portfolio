<?php

namespace App\Portfolio\Consolidator;

use App\Model\Stock\Stock;
use App\User;
use Illuminate\Support\Facades\Auth;

class ConsolidatorExecutor {

    public static function execute(User $user): void {
        self::loginUserAndChangeToConsolidatingState($user);

        self::executeSystemSection();
        self::executeUserSection();

        ConsolidatorStateMachine::getConsolidatorStateMachine()->changeToConsolidatedState();
    }

    private static function loginUserAndChangeToConsolidatingState(User $user) {
        Auth::login($user);
        ConsolidatorStateMachine::getConsolidatorStateMachine()->changeToConsolidatingState();
    }

    private static function executeSystemSection() {
        Stock::updateInfosForAllStocks();
    }

    private static function executeUserSection() {
        ConsolidatorCoordinator::consolidate();
    }
}
