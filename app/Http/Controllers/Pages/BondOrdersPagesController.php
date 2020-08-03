<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Bond\Bond;
use App\Model\Bond\BondOrder;
use App\Portfolio\Utils\ReturnRate;
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
        ];

        return view(self::DEFAULT_DIR . ".create")
            ->with($data);
    }

    private function buildBondOrdersArray(): Collection {
        $bond_orders = BondOrder::getBaseQuery()->get();

        /** @var BondOrder $bond_order */
        foreach ($bond_orders as &$bond_order) {
            $bond = Bond::find($bond_order->bond_id);
            $bond_order->bond_name = $this->generateBondName($bond);
        }

        return $bond_orders;
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
}
