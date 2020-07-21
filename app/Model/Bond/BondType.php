<?php

namespace App\Model\Bond;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Bond\BondType
 *
 * @property int $id
 * @property string $type
 * @property string $description
 */

class BondType extends Model {

    public const TESOURO_DIRETO_ID = 1;
    public const CDB_ID = 2;
    public const LC_ID = 3;
    public const LCI_ID = 4;
    public const LCA_ID = 5;
    public const CRI_ID = 6;
    public const CRA_ID = 7;

    public const TESOURO_DIRETO_TYPE = 'Tesouro Direto';
    public const CDB_TYPE = 'CDB';
    public const LC_TYPE = 'LC';
    public const LCI_TYPE = 'LCI';
    public const LCA_TYPE = 'LCA';
    public const CRI_TYPE = 'CRI';
    public const CRA_TYPE = 'CRA';
}
