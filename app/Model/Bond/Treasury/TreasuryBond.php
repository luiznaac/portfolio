<?php

namespace App\Model\Bond\Treasury;

use App\Model\Index\Index;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Bond\Treasury\TreasuryBond
 *
 * @property int $id
 * @property int $bond_type_id
 * @property int $index_id
 * @property float $index_rate
 * @property float $interest_rate
 * @property string $maturity_date
 */

class TreasuryBond extends Model {

    protected $fillable = [
        'index_id',
        'index_rate',
        'interest_rate',
        'maturity_date',
    ];

    public static function store(
        ?Index $index,
        ?float $index_rate,
        ?float $interest_rate,
        Carbon $maturity_date
    ): self {
        $treasury_bond = new self();
        $treasury_bond->index_id = $index ? $index->id : null;
        $treasury_bond->index_rate = $index_rate;
        $treasury_bond->interest_rate = $interest_rate;
        $treasury_bond->maturity_date = $maturity_date;
        $treasury_bond->save();

        return $treasury_bond;
    }

    public function getTreasuryBondName(): string {
        $name = 'Tesouro';
        $year = Carbon::parse($this->maturity_date)->year;

        if(!$this->index_id) {
            return $name . ' Prefixado ' . $year;
        }

        if($this->index_id == Index::IPCA_ID) {
            return $name . ' IPCA+ ' . $year;
        }

        if($this->index_id == Index::SELIC_ID) {
            return $name . ' Selic ' . $year;
        }

        return 'Treasury Bond';
    }

    public static function getAllTreasuryBondsFromCache(): array {
        $treasury_bonds = self::query()->get();

        $treasury_bonds_cache = [];
        /** @var self $treasury_bond */
        foreach ($treasury_bonds as $treasury_bond) {
            $treasury_bonds_cache[$treasury_bond->id] = $treasury_bond;
        }

        return $treasury_bonds_cache;
    }
}
