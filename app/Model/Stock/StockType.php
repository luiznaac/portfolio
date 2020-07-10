<?php

namespace App\Model\Stock;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Stock\StockType
 *
 * @property int $id
 * @property string $type
 * @property string $description
 */

class StockType extends Model {

    public const ACAO_ID = 1;
    public const ETF_ID = 2;
    public const FII_ID = 3;

    public const ACAO_TYPE = 'Ação';
    public const ETF_TYPE = 'ETF';
    public const FII_TYPE = 'FII';

    public static function getStockTypeByType(string $type): self {
        return self::query()->where('type', $type)->get()->first();
    }

    public static function getStockTypeFromCache(): array {
        $stock_types = self::query()->get();

        $types = [];
        /** @var StockType $type */
        foreach ($stock_types as $type) {
            $types[$type->id] = [
                'type' => $type->type,
                'description' => $type->description,
            ];
        }

        return $types;
    }
}
