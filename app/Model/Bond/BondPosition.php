<?php

namespace App\Model\Bond;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Bond\BondPosition
 *
 * @property int $id
 * @property int $user_id
 * @property int $bond_order_id
 * @property string $date
 * @property float $amount
 * @property float $contributed_amount
 */

class BondPosition extends Model {

    protected $fillable = [
        'user_id',
        'bond_order_id',
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

    public static function getLastBondPositions(): array {
        $bond_order_ids = self::getConsolidatedBondOrderIds();

        $last_bond_positions = [];
        foreach ($bond_order_ids as $bond_order_id) {
            $last_bond_positions[] = self::getLastPositionForBondOrder($bond_order_id);
        }

        return $last_bond_positions;
    }

    public static function getPositionsForBondOrder(BondOrder $bond_order): Collection {
        return self::getBaseQuery()
            ->where('bond_order_id', $bond_order->id)
            ->orderByDesc('date')
            ->get();
    }

    private static function getConsolidatedBondOrderIds(): array {
        $cursor = self::getBaseQuery()
            ->select('bond_order_id')->distinct()
            ->leftJoin('bond_orders', 'bond_orders.id', '=', 'bond_positions.bond_order_id')
            ->get();

        $bond_order_ids = [];
        foreach ($cursor as $data) {
            $bond_order_ids[] = $data->bond_order_id;
        }

        return $bond_order_ids;
    }

    private static function getLastPositionForBondOrder(int $bond_order_id): BondPosition {
        return self::getBaseQuery()
            ->where('bond_order_id', $bond_order_id)
            ->orderByDesc('date')
            ->limit(1)
            ->get()->first();
    }
}
