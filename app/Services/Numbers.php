<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class Numbers
{
    public static function next(string $prefix, string $table, string $column): string
    {
        $date = now()->format('Ymd');
        $last = DB::table($table)
            ->where($column, 'like', "{$prefix}-{$date}-%")
            ->orderByDesc('id')
            ->value($column);

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $next);
    }
}

