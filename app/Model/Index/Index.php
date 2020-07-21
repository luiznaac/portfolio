<?php

namespace App\Model\Index;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Index\Index
 *
 * @property int $id
 * @property string $index
 * @property string $description
 */

class BondType extends Model {

    public const SELIC_ID = 1;
    public const CDI_ID = 2;
    public const IPCA_ID = 3;

    public const SELIC_INDEX = 'Selic';
    public const CDI_INDEX = 'CDI';
    public const IPCA_INDEX = 'IPCA';
}
