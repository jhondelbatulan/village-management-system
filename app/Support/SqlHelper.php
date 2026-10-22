<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class SqlHelper
{
    /**
     * Build a date-formatting expression that works on both MySQL (DATE_FORMAT)
     * and SQLite (strftime) so analytics queries run on either database driver.
     */
    public static function dateFormat(string $column, string $format): string
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            return "DATE_FORMAT({$column}, '{$format}')";
        }

        return "strftime('{$format}', {$column})";
    }
}
