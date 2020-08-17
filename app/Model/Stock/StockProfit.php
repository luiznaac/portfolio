<?php

namespace App\Model\Stock;

use App\Model\Stock\Stock;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Stock\StockProfit
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property float $amount
 */

class StockProfit extends Model {

    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
    ];

    public static function getBaseQuery(): Builder {
        /** @var User $user */
        $user = User::find(auth()->id());

        return $user->stockProfits()->getQuery();
    }

    public function user() {
        return $this->belongsTo('App\User');
    }
}
