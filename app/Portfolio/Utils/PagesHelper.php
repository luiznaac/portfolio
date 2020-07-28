<?php

namespace App\Portfolio\Utils;

use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\ConsolidatorCoordinator;
use App\Portfolio\Consolidator\ConsolidatorStateMachine;

class PagesHelper {

    public static function shouldUpdatePositions(): bool {
        $consolidator_state_machine = ConsolidatorStateMachine::getConsolidatorStateMachine();
        $consolidator_state_machine->updateState();

        return !$consolidator_state_machine->isConsolidated();
    }

    public static function update(): void {
        Stock::updateInfosForAllStocks();
        ConsolidatorCoordinator::consolidate();
    }
}
