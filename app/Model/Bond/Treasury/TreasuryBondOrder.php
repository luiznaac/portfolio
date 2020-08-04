<?php

namespace App\Model\Bond\Treasury;

use App\Portfolio\Consolidator\ConsolidatorStateMachine;
use App\Portfolio\Utils\Calendar;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Bond\Treasury\TreasuryBondOrder
 *
 * @property int $id
 * @property int $user_id
 * @property int $treasury_bond_id
 * @property string $date
 * @property string $type
 * @property float $amount
 */

class TreasuryBondOrder extends Model {

    protected $fillable = [
        'user_id',
        'treasury_bond_id',
        'date',
        'type',
        'amount',
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public static function createOrder(
        TreasuryBond $treasury_bond,
        Carbon $date,
        string $type,
        float $amount
    ): void {
        self::query()->create([
            'user_id' => auth()->id(),
            'treasury_bond_id' => $treasury_bond->id,
            'date' => $date,
            'type' => $type,
            'amount' => $amount,
        ]);

        $last_market_working_date = Calendar::getLastMarketWorkingDate();

        if($date->lt($last_market_working_date)) {
            ConsolidatorStateMachine::getConsolidatorStateMachine()->changeToNotConsolidatedState();
        }
    }

    public static function getBaseQuery(): Builder {
        /** @var User $user */
        $user = User::find(auth()->id());

        return $user->treasuryBondOrders()->getQuery();
    }

    public static function getAllOrdersForTreasuryBondInRange(TreasuryBond $treasury_bond, Carbon $start_date, Carbon $end_date): Collection {
        return self::getBaseQuery()
            ->where('treasury_bond_id', $treasury_bond->id)
            ->whereBetween('date', [$start_date, $end_date])
            ->orderBy('date')
            ->orderBy('type')
            ->get();
    }
}
