<?php

namespace App\Portfolio\Utils;

use App\Portfolio\Consolidator\ConsolidatorStateMachine;

class PagesHelper {

    public static function updateAndGetConsolidationState(): int {
        $consolidator_state_machine = ConsolidatorStateMachine::getConsolidatorStateMachine();
        $consolidator_state_machine->updateState();

        return $consolidator_state_machine->state;
    }
}
