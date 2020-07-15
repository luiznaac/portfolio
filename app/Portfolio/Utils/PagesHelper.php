<?php

namespace App\Portfolio\Utils;

use App\Model\Order\Order;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\StockConsolidator;
use Carbon\Carbon;

class PagesHelper {

    public static function shouldUpdatePositions(): bool {
        $last_order = Order::getBaseQuery()->orderByDesc('id')->get()->first();
        $last_stock_position_infos = StockPosition::getBaseQuery()
            ->selectRaw('MAX(date) AS max_date, MAX(updated_at) AS max_updated_at')
            ->get()->toArray()[0];

        if(!$last_order && !$last_stock_position_infos['max_date']) {
            return false;
        }

        if((!$last_order && $last_stock_position_infos['max_date']) || ($last_order && !$last_stock_position_infos['max_date'])) {
            return true;
        }

        $order_updated_at = Carbon::parse($last_order->updated_at);
        $stock_position_updated_at = Carbon::parse($last_stock_position_infos['max_updated_at']);

        $stock_position_date = Carbon::parse($last_stock_position_infos['max_date']);
        $last_reference_date = Calendar::getLastMarketWorkingDate();

        return $order_updated_at->isAfter($stock_position_updated_at)
            || $stock_position_date->isBefore($last_reference_date);
    }

    public static function update(): void {
        Stock::updateInfosForAllStocks();
        StockConsolidator::consolidate();
    }
}
