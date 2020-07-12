<?php

namespace App\Model\Stock;

use App\Portfolio\Providers\StockDividendProvider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Stock\StockDividend
 *
 * @property int $id
 * @property int $stock_id
 * @property string $type
 * @property string $date_paid
 * @property string $reference_date
 * @property float $value
 */

class StockDividend extends Model {

    protected $fillable = [
        'stock_id',
        'type',
        'date_paid',
        'reference_date',
        'value',
    ];

    public static function getStockDividendsForDateRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        self::loadDividendsForDatesAndStore($stock, $start_date, $end_date);

        return self::getDividendsStoredInRange($stock, $start_date, $end_date);
    }

    public static function loadDividendsForDatesAndStore(Stock $stock, Carbon $start_date, Carbon $end_date): void {
        $dividends = StockDividendProvider::getDividendsForRange($stock, $start_date, $end_date);

        foreach ($dividends as $date => $price) {
            self::query()->updateOrCreate(
                [
                    'stock_id'  => $stock->id,
                    'date'      => $date,
                ],
                [
                    'price' => $price,
                ]
            );
        }
    }

    private static function getDividendsStoredInRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        return self::query()
            ->where('stock_id', $stock->id)
            ->whereBetween('date_paid', [$start_date, $end_date])
            ->get()->toArray();
    }
}
