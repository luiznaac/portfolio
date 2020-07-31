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

class Index extends Model {

    public const SELIC_ID = 1;
    public const CDI_ID = 2;
    public const IPCA_ID = 3;

    public const SELIC_INDEX = 'Selic';
    public const CDI_INDEX = 'CDI';
    public const IPCA_INDEX = 'IPCA';

    public const DAILY = 'daily';
    public const MONTHLY = 'monthly';

    private const ID_INDEX = [
        self::SELIC_ID => self::SELIC_INDEX,
        self::CDI_ID => self::CDI_INDEX,
        self::IPCA_ID => self::IPCA_INDEX,
    ];

    private const ID_FREQUENCY = [
        self::SELIC_ID => self::DAILY,
        self::CDI_ID => self::DAILY,
        self::IPCA_ID => self::MONTHLY,
    ];

    public static function getIndexAbbr(int $index_id): string {
        return self::ID_INDEX[$index_id];
    }

    public static function getIndexFrequency(int $index_id): string {
        return self::ID_FREQUENCY[$index_id];
    }

    public function getAbbr(): string {
        return self::ID_INDEX[$this->id];
    }

    public function getFrequency(): string {
        return self::ID_FREQUENCY[$this->id];
    }
}
