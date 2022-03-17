<?php

use Illuminate\Support\Str;

// if (! function_exists('joyVoyagerImport')) {
//     /**
//      * Helper
//      */
//     function joyVoyagerImport($argument1 = null)
//     {
//         //
//     }
// }

if (!function_exists('isInPatterns')) {
    /**
     * Helper
     */
    function isInPatterns($key, $patterns)
    {
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $key)) {
                return true;
            }
        }
        return false;
    }
}
