<?php

namespace App\Model\Order;

use App\Model\Stock\Stock;
use App\Portfolio\Consolidator\ConsolidatorStateMachine;
use App\Portfolio\Utils\Calendar;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Order\Order
 *
 * @property int $id
 * @property int $user_id
 * @property int $stock_id
 * @property string $date
 * @property string $type
 * @property int $quantity
 * @property float $price
 * @property float $cost
 * @property float $average_price
 */

class Order extends Model {

    protected $fillable = [
        'user_id',
        'stock_id',
        'date',
        'type',
        'quantity',
        'price',
        'cost',
        'average_price',
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public static function getBaseQuery(): Builder {
        /** @var User $user */
        $user = User::find(auth()->id());

        return $user->orders()->getQuery();
    }

    public function store(
        Stock $stock,
        Carbon $date,
        string $type,
        int $quantity,
        float $price,
        float $cost
    ): void {
        $this->stock_id = $stock->id;
        $this->user_id = auth()->id();
        $this->date = $date->toDateString();
        $this->type = $type;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->cost = $cost;
        $this->average_price = $this->calculateAveragePrice();
        $this->save();
    }

    public static function createOrder(
        string $stock_symbol,
        Carbon $date,
        string $type,
        int $quantity,
        float $price,
        float $cost
    ): self {
        $stock = Stock::firstOrCreate(['symbol' => $stock_symbol]);

        $order = new Order();
        $order->store(
            $stock,
            $date,
            $type,
            $quantity,
            $price,
            $cost
        );

        $last_market_working_date = Calendar::getLastMarketWorkingDate();

        if($date->lt($last_market_working_date)) {
            ConsolidatorStateMachine::getConsolidatorStateMachine()->changeToNotConsolidatedState();
        }
        return $order;
    }

    public function getStockSymbol(): string {
        $stock = Stock::find($this->stock_id);

        return $stock->symbol;
    }

    public function getTotal(): string {
        return $this->calculateTotal();
    }

    public static function getAllStocksWithOrders(): array {
        $cursor = self::getBaseQuery()
            ->select('stock_id')->distinct()->get();

        $stocks = [];
        foreach ($cursor as $data) {
            $stocks[] = Stock::find($data->stock_id);
        }

        return $stocks;
    }

    public static function consolidateQuantityForStock(int $stock_id): int {
        $orders = self::getBaseQuery()
            ->where('stock_id', $stock_id)->get();

        $quantity = 0;
        /** @var Order $order */
        foreach ($orders as $order) {
            $type_modifier = self::getTypeModifier($order->type);
            $quantity += $order->quantity * $type_modifier;
        }

        return $quantity;
    }

    public static function getDateOfFirstContribution(Stock $stock = null): ?Carbon {
        $query = self::getBaseQuery();

        if($stock) {
            $query->where('stock_id', $stock->id);
        }

        /** @var Order $order */
        $order = $query->orderBy('date')->get()->first();

        return $order ? Carbon::parse($order->date) : null;
    }

    public static function getAllOrdersForStockUntilDate(Stock $stock, Carbon $date): Collection {
        return self::getBaseQuery()
            ->where('stock_id', $stock->id)
            ->where('date', '<=', $date->toDateString())
            ->orderBy('date')
            ->get();
    }

    public static function getAllOrdersForStockInRage(Stock $stock, Carbon $start_date, Carbon $end_date): Collection {
        return self::getBaseQuery()
            ->where('stock_id', $stock->id)
            ->whereBetween('date', [$start_date, $end_date])
            ->orderBy('date')
            ->orderBy('type')
            ->get();
    }

    public function delete() {
        parent::delete();
        self::touchLastOrder($this);
        ConsolidatorStateMachine::getConsolidatorStateMachine()->changeToNotConsolidatedState();
    }

    public static function getTypeModifier(string $type): int {
        if($type == 'sell') {
            return -1;
        }

        return 1;
    }

    private static function touchLastOrder(Order $order): void {
        /** @var Order $last_order */
        $last_order = self::getBaseQuery()
            ->where('stock_id', $order->stock_id)
            ->where('date', '<=', $order->date)
            ->orderByDesc('date')->get()->first();

        if($last_order) {
            $last_order->touch();
            return;
        }

        /** @var Order $last_order */
        $next_order = self::getBaseQuery()
            ->where('stock_id', $order->stock_id)
            ->where('date', '>', $order->date)
            ->orderBy('date')->get()->first();

        if($next_order) {
            $next_order->touch();
        }
    }

    private function calculateAveragePrice(): float {
        return $this->getTotal() / $this->quantity;
    }

    private function calculateTotal(): float {
        $type_modifier = self::getTypeModifier($this->type);

        return (($this->quantity * $this->price) + ($this->cost * $type_modifier));
    }
}
