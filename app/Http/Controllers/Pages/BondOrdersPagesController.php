<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Bond\Bond;
use App\Model\Bond\BondOrder;
use App\Model\Bond\Treasury\TreasuryBond;
use App\Model\Bond\Treasury\TreasuryBondOrder;
use App\Portfolio\Utils\ReturnRate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class BondOrdersPagesController extends Controller
{
    const DEFAULT_DIR = 'pages.bonds.orders';

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(): View {
        $data = [
            'bond_orders' => $this->buildBondOrdersArray(),
        ];

        return view(self::DEFAULT_DIR . ".index")
            ->with($data);
    }

    public function create(): View {
        $data = [
            'bonds' => $this->buildBondsArray(),
            'treasury_bonds' => $this->buildTreasuryBondsArray(),
        ];

        return view(self::DEFAULT_DIR . ".create")
            ->with($data);
    }

    private function buildBondOrdersArray(): array {
        $bond_orders = BondOrder::getBaseQuery()->get();

        /** @var BondOrder $bond_order */
        foreach ($bond_orders as &$bond_order) {
            $bond = Bond::find($bond_order->bond_id);
            $bond_order->bond_name = $this->generateBondName($bond);
        }

        $treasury_bond_orders = TreasuryBondOrder::getBaseQuery()->get();

        /** @var TreasuryBondOrder $treasury_bond_order */
        foreach ($treasury_bond_orders as &$treasury_bond_order) {
            $treasury_bond = TreasuryBond::find($treasury_bond_order->treasury_bond_id);
            $treasury_bond_order->bond_name = $treasury_bond->getTreasuryBondName();
        }

        $orders = array_merge($bond_orders->toArray(), $treasury_bond_orders->toArray());

        usort($orders, function ($order_1, $order_2) {
            $date_1 = Carbon::parse($order_1['date']);
            $date_2 = Carbon::parse($order_2['date']);

            return $date_1->lt($date_2);
        });

        return $orders;
    }

    private function buildBondsArray(): array {
        $bonds = Bond::all();

        return $this->generateArray($bonds);
    }

    private function generateArray(Collection $collection): array {
        $data[0] = '';

        /** @var Bond $item */
        foreach ($collection as $item) {
            $data[$item->id] = $this->generateBondName($item);
        }

        return $data;
    }

    private function generateBondName(Bond $bond): string {
        $return_rate_string = ReturnRate::getReturnRateString(
            $bond->index_id,
            $bond->index_rate,
            $bond->interest_rate
        );

        return $bond->getBondName() . ' - ' . $return_rate_string;
    }

    private function buildTreasuryBondsArray(): array {
        $treasury_bonds = TreasuryBond::all();

        $data[0] = '';

        /** @var TreasuryBond $item */
        foreach ($treasury_bonds as $item) {
            $data[$item->id] = $item->getTreasuryBondName();
        }

        return $data;
    }
}
