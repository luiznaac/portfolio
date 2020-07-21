<?php

namespace App\Model\Bond;

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

}
