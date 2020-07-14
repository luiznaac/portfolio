<?php

namespace App\Model\Log;

use App\Model\Stock\Stock;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Log\Log
 *
 * @property int $id
 * @property string $type
 * @property string $source
 * @property string $message
 */

class Log extends Model {

    protected $fillable = ['type', 'source', 'message'];

    public const EXCEPTION_TYPE = 'exception';

    public static function log(string $type, string $source, string $message): void {
        self::query()->create([
            'type' => $type,
            'source' => $source,
            'message' => $message,
        ]);
    }
}
