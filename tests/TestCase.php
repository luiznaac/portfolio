<?php

namespace Tests;

use App\Model\Bond\Bond;
use App\Model\Bond\BondIssuer;
use App\Model\Bond\BondOrder;
use App\Model\Bond\BondPosition;
use App\Model\Holiday\Holiday;
use App\Model\Order\Order;
use App\Model\Stock\Dividend\StockDividend;
use App\Model\Stock\Dividend\StockDividendStatementLine;
use App\Model\Stock\Position\StockPosition;
use App\Model\Stock\Stock;
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

    public function saveDividendLines(array $data): void {
        foreach ($data as $item) {
            $item['user_id'] = $item['user_id'] ?? auth()->id();
            $this->extractDividendByReferenceDateIfSet($item);
            $this->setTimestamps($item);

            StockDividendStatementLine::query()->insert($item);
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
            $item['maturity_date'] = Carbon::now();

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

    public function saveBondOrders(array $data): void {
        foreach ($data as $item) {
            $item['user_id'] = $item['user_id'] ?? auth()->id();
            $item['amount'] = $item['amount'] ?? rand(1000, 100000);
            $this->setTimestamps($item);

            BondOrder::query()->insert($item);
        }
    }

    public function translateBondNamesToIds(array &$data, array $bonds_names): void {
        foreach ($data as &$item) {
            $bond_name = $item['bond_name'];
            unset($item['bond_name']);
            $item['bond_id'] = $bonds_names[$bond_name]->id;
        }
    }

    public function translateBondNamesToIdsForDates(array &$expected_dates, array $bonds_names): void {
        foreach ($expected_dates as $bond_name => $expected_date) {
            unset($expected_dates[$bond_name]);
            $expected_dates[$bonds_names[$bond_name]->id] = $expected_date;
        }
    }

    public function translateStockSymbolsToIdsForDates(array &$expected_dates): void {
        foreach ($expected_dates as $symbol => $expected_date) {
            $stock = Stock::getStockBySymbol($symbol);
            unset($expected_dates[$symbol]);
            $expected_dates[$stock->id] = $expected_date;
        }
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
