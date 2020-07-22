<?php

namespace App\Model\Bond;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Bond\BondOrder
 *
 * @property int $id
 * @property int $user_id
 * @property int $bond_id
 * @property string $date
 * @property string $type
 * @property float $amount
 */

class BondOrder extends Model {

    protected $fillable = [
        'user_id',
        'bond_id',
        'date',
        'type',
        'amount',
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public static function createOrder(
        Bond $bond,
        Carbon $date,
        string $type,
        int $amount
    ): void {
        self::query()->create([
            'user_id' => auth()->id(),
            'bond_id' => $bond->id,
            'date' => $date,
            'type' => $type,
            'amount' => $amount,
        ]);
    }

    public static function getBaseQuery(): Builder {
        /** @var User $user */
        $user = User::find(auth()->id());

        return $user->bondOrders()->getQuery();
    }
}
