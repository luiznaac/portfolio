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
        $last_stock_position = StockPosition::getBaseQuery()->orderByDesc('updated_at')->get()->first();

        if(!$last_order && !$last_stock_position) {
            return false;
        }

        if((!$last_order && $last_stock_position) || ($last_order && !$last_stock_position)) {
            return true;
        }

        $order_updated_at = Carbon::parse($last_order->updated_at);
        $stock_position_updated_at = Carbon::parse($last_stock_position->updated_at);

        $stock_position_date = Carbon::parse($last_stock_position->date);
        $last_reference_date = Calendar::getLastMarketWorkingDate();

        return $order_updated_at->isAfter($stock_position_updated_at)
            || $stock_position_date->isBefore($last_reference_date);
    }

    public static function update(): void {
        Stock::updateInfosForAllStocks();
        StockConsolidator::updateLastPositions();
    }
}
