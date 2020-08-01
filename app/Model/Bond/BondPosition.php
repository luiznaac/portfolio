<?php

namespace App\Model\Bond;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Bond\BondPosition
 *
 * @property int $id
 * @property int $user_id
 * @property int $bond_id
 * @property string $date
 * @property float $amount
 * @property float $contributed_amount
 */

class BondPosition extends Model {

    protected $fillable = [
        'user_id',
        'bond_id',
        'date',
        'amount',
        'contributed_amount',
        'created_at',
        'updated_at',
    ];

    public static function getBaseQuery(): Builder {
        /** @var User $user */
        $user = User::find(auth()->id());

        return $user->bondPositions()->getQuery();
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public static function getPositionsForBond(Bond $bond): Collection {
        return self::getBaseQuery()
            ->where('bond_id', $bond->id)
            ->orderByDesc('date')
            ->get();
    }

    public static function getPositionsForBondInRange(Bond $bond, Carbon $start_date, Carbon $end_date): Collection {
        return self::getBaseQuery()
            ->where('bond_id', $bond->id)
            ->whereBetween('date', [$start_date, $end_date])
            ->get();
    }

    private static function getConsolidatedBondIds(): array {
        $cursor = self::getBaseQuery()
            ->select('bond_id')->distinct()
            ->leftJoin('bonds', 'bonds.id', '=', 'bond_positions.bond_id')
            ->get();

        $bond_ids = [];
        foreach ($cursor as $data) {
            $bond_ids[] = $data->bond_id;
        }

        return $bond_ids;
    }
}
