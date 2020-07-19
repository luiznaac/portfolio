<?php

namespace Tests;

use App\Model\Order\Order;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;
    use InteractsWithAuthentication;

    public function loginWithFakeUser(): User {
        /** @var User $user */
        $user = User::query()->create([
            'name' => 'Fake User',
            'email' => rand() . '@fake.com',
            'password' => 'aaaa',
        ]);

        $this->be($user);

        return $user;
    }

    public function saveStockPositions(array $data): void {
        foreach ($data as $item) {
            $this->extractStockAndUnsetStockSymbol($item);
            $item['user_id'] = $item['user_id'] ?? auth()->id();
            $item['quantity'] = $item['quantity'] ?? rand(1, 100);
            $item['amount'] = $item['amount'] ?? (rand(100, 10000)/100 * $item['quantity']);
            $item['contributed_amount'] = $item['contributed_amount'] ?? (rand(100, 700)/100 + $item['amount']);
            $item['average_price'] = $item['average_price'] ?? ($item['contributed_amount'] / $item['quantity']);
            $this->setTimestamps($item);

            StockPosition::query()->insert($item);
        }
    }

    public function saveOrders(array $data): void {
        foreach ($data as $item) {
            $this->extractStockAndUnsetStockSymbol($item);
            $item['user_id'] = $item['user_id'] ?? auth()->id();
            $item['quantity'] = $item['quantity'] ?? rand(1, 100);
            $item['price'] = $item['price'] ?? rand(100, 10000);
            $item['cost'] = $item['cost'] ?? rand(100, 700)/100;
            $item['average_price'] = $item['average_price'] ?? (($item['price'] * $item['quantity'] + $item['cost']) / $item['quantity']);
            $this->setTimestamps($item);

            Order::query()->insert($item);
        }
    }

    private function extractStockAndUnsetStockSymbol(array &$item): void {
        $stock = Stock::getStockBySymbol($item['stock_symbol']);
        $item['stock_id'] = $stock->id;

        unset($item['stock_symbol']);
    }

    private function setTimestamps(array &$item): void {
        $item['created_at'] = $item['created_at'] ?? Carbon::now()->toDateTimeString();
        $item['updated_at'] = $item['updated_at'] ?? Carbon::now()->toDateTimeString();
    }
}
