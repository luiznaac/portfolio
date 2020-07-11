<?php

namespace App\Model\Stock;

use App\Portfolio\Providers\StockPriceProvider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Stock\StockPrice
 *
 * @property int $id
 * @property int $stock_id
 * @property string $date
 * @property float $price
 */

class StockPrice extends Model {

    protected $fillable = [
        'stock_id',
        'date',
        'price',
    ];

    public static function loadPriceForDateAndStore(Stock $stock, Carbon $date): ?self {
        $price = StockPriceProvider::getPriceForDate($stock, $date);

        if(!$price) {
            return null;
        }

        /** @var StockPrice $stock_price */
        $stock_price = self::query()->updateOrCreate(
            [
                'stock_id'  => $stock->id,
                'date'      => $date->toDateString(),
            ],
            [
                'price' => $price,
            ]
        );

        return $stock_price;
    }

    public static function loadPricesForDatesAndStore(Stock $stock, Carbon $start_date, Carbon $end_date): void {
        $prices = StockPriceProvider::getPricesForRange($stock, $start_date, $end_date);

        foreach ($prices as $date => $price) {
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
}
