<?php

namespace App\Libraries;

use App\Exceptions\QpickHttpException;
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
                if (is_array($val) || is_object($val)) {
                    $res[Str::camel($key)] = self::toCamelCase(collect($val));
                } else {
                    $res[Str::camel($key)] = $val;
                }
            }
        }

        return collect($res);
    }

    public static function getBySort(string $sort, array $possibleKey = []): Collection
    {
        $res = collect();
        $sortExp = explode(',', $sort);
        foreach ($sortExp as $v) {
            if ( !isset($v) || !$v ) {
                continue;
            }

            $key = preg_replace("/\-|\+|\s/", '', $v);

            if (!in_array($key, $possibleKey)) {
                throw new QpickHttpException(422, 'common.bad_request', 'sortBy.' . $key);
            }

            if(Str::contains($v, '-')) {
                $res->push(['key' => $key, 'value' => 'DESC']);
            } else{
                $res->push(['key' => $key, 'value' => 'ASC']);
            }
        }

        return $res;
    }

}
