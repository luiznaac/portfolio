<?php

namespace Tests;

use App\Model\Bond\Bond;
use App\Model\Bond\BondIssuer;
use App\Model\Bond\BondOrder;
use App\Model\Bond\BondPosition;
use App\Model\Bond\Treasury\TreasuryBond;
use App\Model\Bond\Treasury\TreasuryBondOrder;
use App\Model\Bond\Treasury\TreasuryBondPosition;
use App\Model\Holiday\Holiday;
use App\Model\Order\Order;
use App\Model\Stock\Dividend\StockDividend;
use App\Model\Stock\Dividend\StockDividendStatementLine;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
use App\Model\Stock\StockProfit;
use App\Portfolio\Consolidator\ConsolidatorDateProvider;
use App\Portfolio\Utils\Calendar;
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

    protected function setUp(): void {
        parent::setUp();
        Holiday::clearCache();
        ConsolidatorDateProvider::clearCache();
    }

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

    public function setTestNowForB3DateTime(string $date_time): void {
        $now_int_utc = Carbon::parse($date_time, Calendar::B3_TIMEZONE)->utc();
        Carbon::setTestNow($now_int_utc);
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
            $this->saveOrder($item);
        }
    }

    public function saveOrdersWithNames(array $data): array {
        $created_orders = [];
        foreach ($data as $item) {
            $order_name = $item['order_name'];
            unset($item['order_name']);

            $created_orders[$order_name] = $this->saveOrder($item);
        }

        return $created_orders;
    }

    public function saveDividendLines(array $data): void {
        foreach ($data as $item) {
            $item['user_id'] = $item['user_id'] ?? auth()->id();
            $this->extractDividendByReferenceDateIfSet($item);
            $this->setTimestamps($item);

            StockDividendStatementLine::query()->insert($item);
        }
    }

    public function saveStockProfits(array $data): void {
        foreach ($data as $item) {
            $item['user_id'] = $item['user_id'] ?? auth()->id();
            $this->setTimestamps($item);

            StockProfit::query()->insert($item);
        }
    }

    public function saveStockDividends(array $data): void {
        foreach ($data as $item) {
            $this->extractStockAndUnsetStockSymbol($item);
            $this->setTimestamps($item);

            StockDividend::query()->insert($item);
        }
    }

    public function saveBonds(array $data): array {
        $created_bonds = [];
        foreach ($data as $item) {
            $created_bonds[] = Bond::query()->create($item);
        }

        return $created_bonds;
    }

    public function saveBondsWithNames(array $data): array {
        $created_bonds = [];
        foreach ($data as $item) {
            $bond_name = $item['bond_name'];
            unset($item['bond_name']);
            $item['bond_issuer_id'] = $this->createBondIssuer()->id;
            $item['bond_type_id'] = rand(2, 7);
            $item['index_id'] = array_key_exists('index_id', $item) ? $item['index_id'] : rand(1, 3);
            $item['index_rate'] = array_key_exists('index_rate', $item) ? $item['index_rate'] : rand(80, 120);
            $item['interest_rate'] = array_key_exists('interest_rate', $item) ? $item['interest_rate'] : rand(0, 15);
            $item['days'] = rand(30, 1460);

            $created_bonds[$bond_name] = Bond::query()->create($item);
        }

        return $created_bonds;
    }

    public function saveBondPositions(array $data): void {
        foreach ($data as $item) {
            $item['user_id'] = $item['user_id'] ?? auth()->id();
            $item['amount'] = $item['amount'] ?? rand(1000, 100000);
            $item['contributed_amount'] = $item['contributed_amount'] ?? (rand(1000, 100000) + $item['amount']);
            $this->setTimestamps($item);

            BondPosition::query()->insert($item);
        }
    }

    public function saveBondOrdersWithNames(array $data): array {
        $created_bond_orders = [];
        foreach ($data as $item) {
            $bond_order_name = $item['bond_order_name'];
            unset($item['bond_name']);
            $item['user_id'] = $item['user_id'] ?? auth()->id();
            $item['date'] = $item['date'] ?? Carbon::now()->addDays(rand(0,30));
            $item['type'] = $item['type'] ?? 'buy';
            $item['amount'] = $item['amount'] ?? rand(1000, 100000);
            $this->setTimestamps($item);

            $created_bond_orders[$bond_order_name] = BondOrder::query()->create($item);
        }

        return $created_bond_orders;
    }

    public function saveTreasuryBonds(array $data): array {
        $created_treasury_bonds = [];
        foreach ($data as $item) {
            $created_treasury_bonds[] = TreasuryBond::query()->create($item);
        }

        return $created_treasury_bonds;
    }

    public function saveTreasuryBondsWithNames(array $data): array {
        $created_treasury_bonds = [];
        foreach ($data as $item) {
            $treasury_bond_name = $item['treasury_bond_name'];
            unset($item['treasury_bond_name']);
            $item['index_id'] = array_key_exists('index_id', $item) ? $item['index_id'] : rand(1, 3);
            $item['interest_rate'] = array_key_exists('interest_rate', $item) ? $item['interest_rate'] : rand(0, 15);
            $item['maturity_date'] = Carbon::now()->addYears(rand(1, 5));

            $created_treasury_bonds[$treasury_bond_name] = TreasuryBond::query()->create($item);
        }

        return $created_treasury_bonds;
    }

    public function saveTreasuryBondPositions(array $data): void {
        foreach ($data as $item) {
            $item['user_id'] = $item['user_id'] ?? auth()->id();
            $item['amount'] = $item['amount'] ?? rand(1000, 100000);
            $item['contributed_amount'] = $item['contributed_amount'] ?? (rand(1000, 100000) + $item['amount']);
            $this->setTimestamps($item);

            TreasuryBondPosition::query()->insert($item);
        }
    }

    public function saveTreasuryBondOrders(array $data): void {
        foreach ($data as $item) {
            $item['user_id'] = $item['user_id'] ?? auth()->id();
            $item['amount'] = $item['amount'] ?? rand(1000, 100000);
            $this->setTimestamps($item);

            TreasuryBondOrder::query()->insert($item);
        }
    }

    public function translateOrderNamesToIds(array &$data, array $order_names): void {
        foreach ($data as &$item) {
            $order_name = $item['order_name'];
            unset($item['order_name']);
            $item['order_id'] = $order_names[$order_name]->id;
        }
    }

    public function translateBondNamesToIds(array &$data, array $bonds_names): void {
        foreach ($data as &$item) {
            $bond_name = $item['bond_name'];
            unset($item['bond_name']);
            $item['bond_id'] = $bonds_names[$bond_name]->id;
        }
    }

    public function translateBondOrderNamesToIds(array &$data, array $bonds_order_names): void {
        foreach ($data as &$item) {
            $bonds_order_name = $item['bond_order_name'];
            unset($item['bond_order_name']);
            $item['bond_order_id'] = $bonds_order_names[$bonds_order_name]->id;
        }
    }

    public function translateBondOrderNamesToIdsForKeys(array &$data, array $bond_orders_names): void {
        foreach ($data as $bond_order_name => $expected_date) {
            unset($data[$bond_order_name]);
            $data[$bond_orders_names[$bond_order_name]->id] = $expected_date;
        }
    }

    public function translateTreasuryBondNamesToIdsForKeys(array &$data, array $treasury_bonds_names): void {
        foreach ($data as $treasury_bond_name => $expected_date) {
            unset($data[$treasury_bond_name]);
            $data[$treasury_bonds_names[$treasury_bond_name]->id] = $expected_date;
        }
    }

    public function translateTreasuryBondNamesToIds(array &$data, array $treasury_bonds_names): void {
        foreach ($data as &$item) {
            $treasury_bond_name = $item['treasury_bond_name'];
            unset($item['treasury_bond_name']);
            $item['treasury_bond_id'] = $treasury_bonds_names[$treasury_bond_name]->id;
        }
    }

    public function translateStockSymbolsToIdsForDates(array &$expected_dates): void {
        foreach ($expected_dates as $symbol => $expected_date) {
            $stock = Stock::getStockBySymbol($symbol);
            unset($expected_dates[$symbol]);
            $expected_dates[$stock->id] = $expected_date;
        }
    }

    private function saveOrder(array $item): Order {
        $this->extractStockAndUnsetStockSymbol($item);
        $item['user_id'] = $item['user_id'] ?? auth()->id();
        $item['date'] = $item['date'] ?? Carbon::now()->addDays(rand(0, 365));
        $item['quantity'] = $item['quantity'] ?? rand(1, 100);
        $item['price'] = $item['price'] ?? rand(100, 10000);
        $item['cost'] = $item['cost'] ?? rand(100, 700)/100;
        $item['average_price'] = $item['average_price'] ?? (($item['price'] * $item['quantity'] + $item['cost']) / $item['quantity']);
        $this->setTimestamps($item);

        return Order::query()->create($item);
    }

    private function createBondIssuer(): BondIssuer {
        return BondIssuer::query()->create([
            'name' => bin2hex(random_bytes(10)),
        ]);
    }

    private function extractStockAndUnsetStockSymbol(array &$item): void {
        $stock = Stock::getStockBySymbol($item['stock_symbol']);
        $item['stock_id'] = $stock->id;

        unset($item['stock_symbol']);
    }

    private function extractDividendByReferenceDateIfSet(array &$item): void {
        if(!isset($item['stock_symbol'])) {
            return;
        }

        $stock = Stock::getStockBySymbol($item['stock_symbol']);
        $stock_dividend = StockDividend::query()
            ->where('stock_id', $stock->id)
            ->where('reference_date', $item['reference_date'])
            ->get()->first();
        $item['stock_dividend_id'] = $stock_dividend->id;

        unset($item['reference_date']);
        unset($item['stock_symbol']);
    }

    private function setTimestamps(array &$item): void {
        $item['created_at'] = $item['created_at'] ?? Carbon::now()->toDateTimeString();
        $item['updated_at'] = $item['updated_at'] ?? Carbon::now()->toDateTimeString();
    }
}
