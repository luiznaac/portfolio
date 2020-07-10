<?php

namespace App\Model\Stock\Position;

use App\Model\Stock\Stock;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Stock\StockPosition
 *
 * @property int $id
 * @property int $user_id
 * @property int $stock_id
 * @property string $date
 * @property int $quantity
 * @property float $amount
 * @property float $contributed_amount
 * @property float $average_price
 */

class StockPosition extends Model {

    protected $fillable = [
        'user_id',
        'stock_id',
        'date',
        'quantity',
        'amount',
        'contributed_amount',
        'average_price',
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public static function getLastStockPositions(): array {
        $stock_ids = self::getConsolidatedStockIds();

        $last_stock_positions = [];
        foreach ($stock_ids as $stock_id) {
            $last_stock_positions[] = self::getLastPositionForStock($stock_id);
        }

        return $last_stock_positions;
    }

    public static function getPositionsForStock(Stock $stock): Collection {
        return self::getBaseQuery()
            ->where('stock_id', $stock->id)
            ->orderByDesc('date')
            ->get();
    }

    private static function getConsolidatedStockIds(): array {
        $cursor = self::getBaseQuery()->select('stock_id')->distinct()->get();

        $stock_ids = [];
        foreach ($cursor as $data) {
            $stock_ids[] = $data->stock_id;
        }

        return $stock_ids;
    }

    private static function getLastPositionForStock(int $stock_id): StockPosition {
        return self::getBaseQuery()
            ->where('stock_id', $stock_id)
            ->orderByDesc('date')
            ->limit(1)
            ->get()->first();
    }

    private static function getBaseQuery(): Builder {
        /** @var User $user */
        $user = User::find(auth()->id());

        return $user->stockPositions()->getQuery();
    }
}