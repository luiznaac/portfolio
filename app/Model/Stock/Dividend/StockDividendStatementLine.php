<?php

namespace App\Model\Stock\Dividend;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Stock\Dividend\StockDividendStatementLine
 *
 * @property int $id
 * @property int $user_id
 * @property int $stock_dividend_id
 * @property int $quantity
 * @property float $amount_paid
 */

class StockDividendStatementLine extends Model {

    protected $fillable = [
        'user_id',
        'stock_dividend_id',
        'quantity',
        'amount_paid',
    ];

    public static function getBaseQuery(): Builder {
        /** @var User $user */
        $user = User::find(auth()->id());

        return $user->stockDividendStatementLines()->getQuery();
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public static function getTotalAmountPaid(): ?float {
        return self::getBaseQuery()->sum('amount_paid');
    }
}
