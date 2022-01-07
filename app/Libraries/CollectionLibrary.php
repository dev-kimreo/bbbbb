<?php

namespace App\Libraries;

use App\Exceptions\QpickHttpException;
use Str;
use Illuminate\Support\Collection;

class CollectionLibrary
{
    public static function toCamelCase(Collection|array $value): Collection
    {
        return self::changeCase($value, 'camel');
    }

    public static function changeCase(Collection|array $value, string $case): Collection
    {
        // Setting Case
        if(!in_array($case, ['camel', 'kebab', 'snake'])) {
            $case = 'camel';
        }

        // Init
        $res = [];

        // Proc
        foreach ($value as $key => $val) {
            if (is_array($val) || is_object($val)) {
                $res[Str::$case($key)] = self::toCamelCase(collect($val));
            } else {
                $res[Str::$case($key)] = $val;
            }
        }

        // Return
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

    public static function hasKeyCaseInsensitive(Collection $target, string $key): ?string
    {
        foreach([$key, Str::snake($key), Str::camel($key)] as $v) {
            if($res = isset($target[$v])? $v: null) {
                break;
            }
        }

        return $res ?? $key;
    }

    public static function replaceValuesByPrefix(Collection $target, string $prefix): Collection
    {
        $res = collect();

        $target->each(function ($item, $key) use ($res, $prefix) {
            $res[$key] = $prefix . '.' . $item;
        });

        return $res;
    }
}
