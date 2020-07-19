<?php

namespace App\Model\Stock\Dividend;

use App\Model\Order\Order;
use App\Model\Stock\Stock;
use App\Model\Stock\StockType;
use App\Portfolio\Providers\StockDividendProvider;
use App\Portfolio\Utils\BatchInsertOrUpdate;
use App\Portfolio\Utils\Calendar;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public static function getOldestDateOfMissingStockDividendStatementLineForEachStock(): array {
        $query = <<<SQL
SELECT MIN(reference_date) AS oldest_missing_date, stock_id
FROM stock_dividends sd
         LEFT JOIN stock_dividend_statement_lines dl on sd.id = dl.stock_dividend_id
WHERE user_id = ?
  AND dl.id IS NULL
  AND sd.date_paid <= ?
GROUP BY stock_id;
SQL;

        $rows = DB::select($query, [auth()->id(), Calendar::getLastMarketWorkingDate()]);

        $data = [];
        foreach ($rows as $row) {
            $data[$row->stock_id] = $row->oldest_missing_date;
        }

        return $data;
    }
}
