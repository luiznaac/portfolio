<?php

namespace App\Portfolio\Consolidator;

use App\Portfolio\Utils\Calendar;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Portfolio\Consolidator\ConsolidatorStateMachine
 *
 * @property int $id
 * @property int $user_id
 * @property int $state
 */
class ConsolidatorStateMachine extends Model {

    protected $fillable = ['user_id', 'state'];

    private const CONSOLIDATED_STATE = 0;
    private const NOT_CONSOLIDATED_STATE = 1;

    public static function getBaseQuery(): Builder {
        /** @var User $user */
        $user = User::find(auth()->id());

        return $user->consolidatorStateMachine()->getQuery();
    }

    public static function getConsolidatorStateMachine(): self {
        /** @var self $consolidator_state_machine */
        $consolidator_state_machine = self::getBaseQuery()->first();

        if($consolidator_state_machine) {
            return $consolidator_state_machine;
        }

        return self::createConsolidatorStateMachine();
    }

    public static function changeAllMachinesToNotConsolidatedState(): void {
        $consolidator_state_machines = self::query()->get();

        /** @var self $machine */
        foreach ($consolidator_state_machines as $machine) {
            $machine->changeToNotConsolidatedState();
        }
    }

    public function updateState(): void {
        if($this->state == self::NOT_CONSOLIDATED_STATE) {
            return;
        }

        $last_reference_date = ConsolidatorDateProvider::getOldestLastReferenceDate();

        if(!$last_reference_date) {
            return;
        }

        $last_market_working_day = Calendar::getLastMarketWorkingDate();

        if($last_reference_date->lt($last_market_working_day)) {
            $this->changeToNotConsolidatedState();
        }
    }

    private static function createConsolidatorStateMachine(): self {
        /** @var self $new_consolidator_state_machine */
        $new_consolidator_state_machine = self::query()->create([
            'user_id' => auth()->id(),
            'state' => self::CONSOLIDATED_STATE,
        ]);

        return $new_consolidator_state_machine;
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function changeToConsolidatedState(): void {
        $this->state = self::CONSOLIDATED_STATE;
        $this->save();
    }

    public function changeToNotConsolidatedState(): void {
        $this->state = self::NOT_CONSOLIDATED_STATE;
        $this->save();
    }

    public function isConsolidated(): bool {
        return $this->state == self::CONSOLIDATED_STATE;
    }
}
