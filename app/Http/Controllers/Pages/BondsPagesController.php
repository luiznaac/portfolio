<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Model\Bond\Bond;
use App\Model\Bond\BondIssuer;
use App\Model\Bond\BondType;
use App\Model\Index\Index;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class BondsPagesController extends Controller
{
    const DEFAULT_DIR = 'pages.bonds';

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(): View {
        $data = [
            'bonds' => Bond::query()
                ->orderBy('bond_type_id')
                ->orderBy('bond_issuer_id')->get(),
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

    private function buildBondIssuersArray(): array {
        $bond_issuers = BondIssuer::all();

        return $this->generateArray($bond_issuers, 'name');
    }

    private function buildBondTypesArray(): array {
        $bond_types = BondType::all();

        return $this->generateArray($bond_types, 'type');
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
