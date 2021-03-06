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
 * @property int $days
 */

class Bond extends Model {

    protected $fillable = [
        'bond_issuer_id',
        'bond_type_id',
        'index_id',
        'index_rate',
        'interest_rate',
        'days',
    ];

    public static function store(
        BondIssuer $bond_issuer,
        BondType $bond_type,
        ?Index $index,
        ?float $index_rate,
        ?float $interest_rate,
        int $days
    ): self {
        $bond = new self();
        $bond->bond_issuer_id = $bond_issuer->id;
        $bond->bond_type_id = $bond_type->id;
        $bond->index_id = $index ? $index->id : null;
        $bond->index_rate = $index_rate;
        $bond->interest_rate = $interest_rate;
        $bond->days = $days;
        $bond->save();

        return $bond;
    }

    public function getBondName(): string {
        $bond_type = BondType::getType($this->bond_type_id);
        $bond_issuer_name = $this->getBondIssuer()->name;

        return "$bond_type $bond_issuer_name - $this->days days";
    }

    public static function getAllBondsFromCache(): array {
        $bonds = self::query()->get();

        $bonds_cache = [];
        /** @var Bond $bond */
        foreach ($bonds as $bond) {
            $bonds_cache[$bond->id] = $bond;
        }

        return $bonds_cache;
    }

    private function getBondIssuer(): BondIssuer{
        return BondIssuer::find($this->bond_issuer_id);
    }
}
