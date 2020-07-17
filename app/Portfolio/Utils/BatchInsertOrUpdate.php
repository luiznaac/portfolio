<?php

namespace App\Portfolio\Utils;

use Illuminate\Support\Facades\DB;

class BatchInsertOrUpdate {

    public static function execute(string $table, array $data): void {
        if(empty($data)) {
            return;
        }

        $statement = self::buildStatement($table, $data);

        $parameters = [];
        foreach ($data as $item) {
            $parameters = array_merge($parameters, array_values($item));
        }

        DB::affectingStatement($statement, $parameters);
    }

    private static function buildStatement(string $table, array $data): string {
        $columns = array_keys($data[0]);
        $statement = self::tableSignature($table, $columns);
        $statement .= self::values($data);
        $statement .= self::onDuplicate($columns);

        return $statement;
    }

    private static function tableSignature(string $table, array $columns): string {
        return 'INSERT INTO ' . $table . '(' . implode(', ', $columns) . ', updated_at, created_at) VALUES ';
    }

    private static function values(array $data): string {
        $values = [];
        foreach ($data as $item) {
            $values[] = '(' . implode(', ', array_fill(0, sizeof($item), '?')) . ', NOW(), NOW())';
        }

        return implode(', ', $values);
    }

    private static function onDuplicate(array $columns): string {
        $on_duplicate_fragment = ' ON DUPLICATE KEY UPDATE ';

        $on_duplicate_fields = [];
        foreach ($columns as $column) {
            $on_duplicate_fields[] = $column . ' = VALUES(' . $column . ')';
        }
        $on_duplicate_fields[] = 'updated_at = VALUES(updated_at)';

        return $on_duplicate_fragment . implode(', ', $on_duplicate_fields);
    }
}
