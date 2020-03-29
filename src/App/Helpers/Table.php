<?php

namespace LaravelEnso\Upgrade\App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class Table
{
    public static function hasIndex(string $table, string $index): bool
    {
        $currentIndexes = Schema::getConnection()->getDoctrineSchemaManager()
            ->listTableIndexes($table);

        return (new Collection($currentIndexes))->has($index);
    }

    public static function hasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }
}
