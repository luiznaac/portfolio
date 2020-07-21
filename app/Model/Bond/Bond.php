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
        $bond->index_id = $index->id;
        $bond->index_rate = $index_rate;
        $bond->interest_rate = $interest_rate;
        $bond->maturity_date = $maturity_date;
        $bond->save();

        return $bond;
    }
}
