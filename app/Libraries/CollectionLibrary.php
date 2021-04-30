<?php

namespace App\Libraries;

use Str;
use Illuminate\Support\Collection;

class CollectionLibrary
{


    public static function toCamelCase(Collection $value): Collection
    {
        $res = [];

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        if (is_array($value) || is_object($value)) {
            foreach ($value as $key => $val) {
                if ( is_array($val) || is_object($val) ) {
                    $res[Str::camel($key)] = self::toCamelCase(collect($val));
                } else {
                    $res[Str::camel($key)] = $val;
                }
            }
        }

        return collect($res);
    }

}
