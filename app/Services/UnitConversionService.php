<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UnitConversionService
{
    public function convert(float $quantity, string $from, string $to): float
    {
        $from = strtolower(trim($from));
        $to = strtolower(trim($to));

        if ($from === $to) {
            return $quantity;
        }

        $builtIn = [
            'g:kg' => 0.001,
            'kg:g' => 1000,
            'ml:l' => 0.001,
            'l:ml' => 1000,
            'pcs:pcs' => 1,
        ];

        if (isset($builtIn["{$from}:{$to}"])) {
            return $quantity * $builtIn["{$from}:{$to}"];
        }

        $factor = DB::table('unit_conversions')
            ->where('from_unit', $from)
            ->where('to_unit', $to)
            ->value('factor');

        if ($factor === null) {
            throw new InvalidArgumentException("Missing unit conversion from {$from} to {$to}.");
        }

        return $quantity * (float) $factor;
    }
}

