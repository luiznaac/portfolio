<?php

namespace Tests\Portfolio\Consolidator;

use App\Portfolio\Consolidator\ConsolidatorCoordinator;
use App\Portfolio\Consolidator\ConsolidatorStateMachine;
use Tests\TestCase;

class ConsolidatorCoordinatorTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        $this->loginWithFakeUser();
    }

    public function testConsolidate_ShouldChangeConsolidatorStateMachineToConsolidatedState(): void {
        $consolidator_state_machine = ConsolidatorStateMachine::getConsolidatorStateMachine();
        $this->assertEquals(1, $consolidator_state_machine->state);

        ConsolidatorCoordinator::consolidate();

        $consolidator_state_machine->refresh();
        $this->assertEquals(0, $consolidator_state_machine->state);
    }
}
