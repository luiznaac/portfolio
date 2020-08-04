<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Bond\Bond;
use App\Model\Bond\BondIssuer;
use App\Model\Bond\BondType;
use App\Model\Bond\Treasury\TreasuryBond;
use App\Model\Index\Index;
use App\Portfolio\Utils\ReturnRate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class BondsPagesController extends Controller
{
    const DEFAULT_DIR = 'pages.bonds';

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(): View {
        $bonds = Bond::query()
            ->orderBy('bond_type_id')
            ->orderBy('bond_issuer_id')->get();
        $treasury_bonds = TreasuryBond::query()
            ->orderByDesc('maturity_date')->get();

        $data = [
            'bonds' => $this->fillRateOfReturn($bonds),
            'treasury_bonds' => $this->fillRateOfReturn($treasury_bonds),
        ];

        return view(self::DEFAULT_DIR . ".index")
            ->with($data);
    }

    public function create(): View {
        $data = [
            'bond_issuers' => $this->buildBondIssuersArray(),
            'bond_types' => $this->buildBondTypesArray(),
            'indices' => $this->buildIndicesArray(),
        ];

        return view(self::DEFAULT_DIR . ".create")
            ->with($data);
    }

    private function fillRateOfReturn(Collection $bonds): array {
        $filled_bonds = [];
        foreach ($bonds as $bond) {
            $return_string = ReturnRate::getReturnRateString($bond['index_id'], $bond['index_rate'], $bond['interest_rate']);
            $bond['return'] = $return_string;
            $filled_bonds[] = $bond;
        }

        return $filled_bonds;
    }

    private function buildBondIssuersArray(): array {
        $bond_issuers = BondIssuer::all();

        return $this->generateArray($bond_issuers, 'name');
    }

    private function buildBondTypesArray(): array {
        $bond_types = BondType::getAll();
        $bond_types[0] = '';
        ksort($bond_types);

        return $bond_types;
    }

    private function buildIndicesArray(): array {
        $indices = Index::all();

        return $this->generateArray($indices, 'index');
    }

    private function generateArray(Collection $collection, string $field): array {
        $data[0] = '';
        foreach ($collection as $item) {
            $data[$item->id] = $item->$field;
        }

        return $data;
    }
}
