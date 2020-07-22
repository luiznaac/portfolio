<?php

namespace App\Model\Bond;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Bond\BondOrder
 *
 * @property int $id
 * @property int $user_id
 * @property int $bond_id
 * @property string $maturity_date
 * @property string $type
 * @property float $amount
 */

class BondOrder extends Model {

    public function user() {
        return $this->belongsTo('App\User');
    }

    public static function getBaseQuery(): Builder {
        /** @var User $user */
        $user = User::find(auth()->id());

        return $user->bondOrders()->getQuery();
    }
}
