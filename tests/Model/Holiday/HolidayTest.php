<?php

namespace Tests\Model\Holiday;

use App\Model\Holiday\Holiday;
use App\Model\Order\Order;
use App\Model\Stock\Stock;
use Carbon\Carbon;
use Tests\TestCase;

class HolidayTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        $this->loginWithFakeUser();
    }

    public function testIsHoliday(): void {
        Carbon::setTestNow('2020-07-09');
        $this->createHolidays(['2017', '2019', '2020']);

        $date_holiday_1 = Carbon::createFromFormat('Y', '2017');
        $date_holiday_2 = Carbon::createFromFormat('Y', '2018');
        $date_holiday_3 = Carbon::createFromFormat('Y', '2019');
        $date_holiday_4 = Carbon::createFromFormat('Y', '2020');

        $this->assertTrue(Holiday::isHoliday($date_holiday_1));
        $this->assertFalse(Holiday::isHoliday($date_holiday_2));
        $this->assertTrue(Holiday::isHoliday($date_holiday_3));
        $this->assertTrue(Holiday::isHoliday($date_holiday_4));
    }

    public function testLoadHolidaysWithMissingYearInBetween_ShouldLoadMissingYear(): void {
        $this->createHolidays(['2017', '2019', '2020']);
        $this->createOrder(Carbon::parse('2017-05-07'));
        Carbon::setTestNow('2020-05-07');
        Holiday::loadHolidays();

        $this->assertHolidays(2017, 1);
        $this->assertHolidays(2018, 16);
        $this->assertHolidays(2019, 1);
        $this->assertHolidays(2020, 1);
    }

    public function testLoadHolidaysWithNewOrder_ShouldLoadMissingYear(): void {
        $this->createHolidays(['2019', '2020']);
        $this->createOrder(Carbon::parse('2020-05-07'));
        $this->createTreasuryBondOrder(Carbon::parse('2018-05-07'));
        Carbon::setTestNow('2020-05-07');
        Holiday::loadHolidays();

        $this->assertHolidays(2018, 16);
        $this->assertHolidays(2019, 1);
        $this->assertHolidays(2020, 1);
    }

    public function testLoadHolidaysWithoutOrders_ShouldNotLoad(): void {
        Carbon::setTestNow('2018-05-07');
        Holiday::loadHolidays();

        $this->assertHolidays(2018, 0);
    }

    private function createHolidays(array $years): void {
        foreach ($years as $year) {
            Holiday::query()->insert([
                'date' => Carbon::createFromFormat('Y', $year)->toDateString(),
                'description' => 'Holiday'
            ]);
        }
    }

    private function createOrder(Carbon $date): void {
        $order_1 = new Order();
        $order_1->store(
            Stock::getStockBySymbol('SQIA3'),
            Carbon::parse($date),
            $type = 'buy',
            $quantity = 10,
            $price = 15.22,
            $cost = 7.50
        );
    }

    private function createTreasuryBondOrder(Carbon $date): void {
        $treasury_bond_names = $this->saveTreasuryBondsWithNames([['treasury_bond_name' => 'Bond 1']]);
        $treasury_bond_orders = [['treasury_bond_name' => 'Bond 1', 'date' => $date, 'type' => 'buy', 'amount' => 5000]];
        $this->translateTreasuryBondNamesToIds($treasury_bond_orders, $treasury_bond_names);
        $this->saveTreasuryBondOrders($treasury_bond_orders);
    }

    private function assertHolidays(int $year, int $count): void {
        $holidays = Holiday::query()->whereRaw("YEAR(date) = '$year'")->get();

        $this->assertCount($count, $holidays);
    }
}
