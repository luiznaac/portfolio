<?php

namespace App\Model\Bond\Treasury;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Bond\Treasury\TreasuryBondPosition
 *
 * @property int $id
 * @property int $user_id
 * @property int $treasury_bond_id
 * @property string $date
 * @property float $amount
 * @property float $contributed_amount
 */

class TreasuryBondPosition extends Model {

    protected $fillable = [
        'user_id',
        'treasury_bond_id',
        'date',
        'amount',
        'contributed_amount',
        'created_at',
        'updated_at',
    ];

    public static function getBaseQuery(): Builder {
        /** @var User $user */
        $user = User::find(auth()->id());

        return $user->treasuryBondPositions()->getQuery();
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public static function getLastTreasuryBondPositions(): array {
        $treasury_bond_ids = self::getConsolidatedTreasuryBondIds();

        $last_treasury_bond_positions = [];
        foreach ($treasury_bond_ids as $treasury_bond_id) {
            $last_treasury_bond_positions[] = self::getLastPositionForTreasuryBond($treasury_bond_id);
        }

        return $last_treasury_bond_positions;
    }

    public static function getPositionsForTreasuryBond(TreasuryBond $treasury_bond): Collection {
        return self::getBaseQuery()
            ->where('treasury_bond_id', $treasury_bond->id)
            ->orderByDesc('date')
            ->get();
    }

    private static function getConsolidatedTreasuryBondIds(): array {
        $cursor = self::getBaseQuery()
            ->select('treasury_bond_id')->distinct()
            ->leftJoin('treasury_bonds', 'treasury_bonds.id', '=', 'treasury_bond_positions.treasury_bond_id')
            ->get();

        $treasury_bond_ids = [];
        foreach ($cursor as $data) {
            $treasury_bond_ids[] = $data->treasury_bond_id;
        }

        return $treasury_bond_ids;
    }

    private static function getLastPositionForTreasuryBond(int $treasury_bond_id): TreasuryBondPosition {
        return self::getBaseQuery()
            ->where('treasury_bond_id', $treasury_bond_id)
            ->orderByDesc('date')
            ->limit(1)
            ->get()->first();
    }
}
