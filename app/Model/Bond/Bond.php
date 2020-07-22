<?php

namespace App\Model\Bond;

use App\Model\Index\Index;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Bond\Bond
 *
 * @property int $id
 * @property int $bond_issuer_id
 * @property int $bond_type_id
 * @property int $index_id
 * @property float $index_rate
 * @property float $interest_rate
 * @property string $maturity_date
 */

class Bond extends Model {

    protected $fillable = [
        'bond_issuer_id',
        'bond_type_id',
        'index_id',
        'index_rate',
        'interest_rate',
        'maturity_date',
    ];

    public static function store(
        BondIssuer $bond_issuer,
        BondType $bond_type,
        ?Index $index,
        ?float $index_rate,
        ?float $interest_rate,
        Carbon $maturity_date
    ): self {
        $bond = new self();
        $bond->bond_issuer_id = $bond_issuer->id;
        $bond->bond_type_id = $bond_type->id;
        $bond->index_id = $index ? $index->id : null;
        $bond->index_rate = $index_rate;
        $bond->interest_rate = $interest_rate;
        $bond->maturity_date = $maturity_date;
        $bond->save();

        return $bond;
    }

    public function getBondName(): string {
        $bond_type = BondType::getType($this->bond_type_id);
        $bond_issuer_name = $this->getBondIssuer()->name;
        $month_year = $this->getMonthYear();

        return "$bond_type $bond_issuer_name - $month_year";
    }

    public function getReturnRateString(): string {
        $return_rate = null;

        if ($this->index_id) {
            $index_abbr = Index::getIndexAbbr($this->index_id);
            $return_rate = $this->index_rate . '% ' . $index_abbr;
        }

        if ($this->interest_rate) {
            $return_rate = ($return_rate ? ($return_rate . ' + ') : '') . $this->interest_rate . '%';
        }

        return $return_rate;
    }

    private function getBondIssuer(): BondIssuer{
        return BondIssuer::find($this->bond_issuer_id);
    }

    private function getMonthYear(): string {
        $date = Carbon::parse($this->maturity_date);

        if ($date->day < 8) {
            $date->subMonth();
        }

        return strtoupper($date->format('M/Y'));
    }
}
