<?php

namespace App\Model\Stock;

use App\Model\Order\Order;
use App\Portfolio\Providers\StockDividendProvider;
use App\Portfolio\Utils\Calendar;
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

    public static function getStockDividendsStoredInRange(Stock $stock, Carbon $start_date, Carbon $end_date): array {
        return self::query()
            ->where('stock_id', $stock->id)
            ->whereBetween('date_paid', [$start_date, $end_date])
            ->get()->toArray();
    }

    public static function loadHistoricDividendsForAllStocks(): void {
        $stocks = Stock::query()->get();

        /** @var Stock $stock */
        foreach ($stocks as $stock) {
            /** @var Order $first_order */
            $first_order = Order::query()
                ->where('stock_id', $stock->id)
                ->orderBy('date')
                ->first();

            if(!$first_order) {
                continue;
            }

            $start_date = Carbon::parse($first_order->date);
            $end_date = Calendar::getLastMarketWorkingDate();

            self::loadDividendsForDatesAndStore($stock, $start_date, $end_date);
        }
    }

    private static function loadDividendsForDatesAndStore(Stock $stock, Carbon $start_date, Carbon $end_date): void {
        $stock_type = $stock->getStockType();

        if ($stock_type->type == StockType::ETF_TYPE) {
            return;
        }

        $dividends = StockDividendProvider::getDividendsForRange($stock, $start_date, $end_date);

        foreach ($dividends as $info => $value) {
            [$date_paid, $reference_date, $type] = explode('|', $info);

            self::query()->updateOrCreate(
                [
                    'stock_id'          => $stock->id,
                    'type'              => $type,
                    'date_paid'         => $date_paid,
                    'reference_date'    => $reference_date,
                ],
                [
                    'value' => $value,
                ]
            );
        }
    }
}
