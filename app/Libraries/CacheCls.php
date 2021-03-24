<?php

namespace App\Libraries;

use Cache;
use Illuminate\Routing\Controller as BaseController;


class CacheCls {
    private static $cacheCls = null;
    public static $tags = null;
    public static $tagArr = [];

    public function __construct() {
    }

    static function reset() {
        self::$tags = null;
        self::$tagArr = [];
    }

    public static function init() {
        if (is_null(self::$cacheCls)) {
            self::$cacheCls = new CacheCls();
        }

//        return self::$cacheCls;
    }

    public static function remember($key, $data, $exp=null) {
        $exp = is_null($exp) ? config('cache.custom.expire.common') : intval($exp);

        if (!isset($key) || !$key ) {
            return false;
        }

        if (isset(self::$tags)) {
            self::$tagArr = self::separateTag();

            Cache::tags(self::$tagArr)->remember($key, $exp, function () use ($data){
                return $data;
            });
        } else {
            Cache::remember($key, $exp, function () use ($data) {
                return $data;
            });
        }

        self::reset();

        return true;
    }

    public static function tags(string $tags){
        self::init();
        self::$tags = $tags;
        return self::$cacheCls;
    }

    public static function get(string $key) {
        self::$tagArr = self::separateTag();

        $getVal = null;
        if ( count(self::$tagArr) ) {
            $getVal = Cache::tags(self::$tagArr)->get($key);
        } else {
            $getVal = Cache::get($key);
        }

        self::reset();

        return $getVal ?? null;
    }

    public static function separateTag($recursive = true) {
        if ( isset(self::$tags) && self::$tags ) {
            $keyExp = explode('.', self::$tags);
            $keyCnt = count($keyExp);
            $tagArr = [];

            if ($keyCnt > 1) {
                for ($i=0; $i<$keyCnt; $i++) {
                    $_tags = [];
                    for ($j=0; $j<=$i; $j++) {
                        $_tags[] =  $keyExp[$j];
                    }
                    $tagArr[] = implode('.', $_tags);
                }
            }

            return $tagArr;
        }

    }


//if ( !function_exists('rememberCache') ) {
//    function rememberCache($tag, $key, $data = null, $exp = null) {
//        $arr = separateTags($tag);
//        $exp = is_null($exp) ? config('cache.custom.expire.common') : intval($exp);
//
//        if ( count($arr['tag']) ) {
//            Cache::tags($arr['tag'])->remember($arr['key'], $exp, function () use ($data){
//                echo '새로';
//                return $data;
//            });
//        } else {
//            Cache::remember($arr['key'], $exp, function () use ($data) {
//                echo '새로';
//                return $data;
//            });
//        }
//    }
//}
//
//if ( !function_exists('getCache') ) {
//    function getCache($tag) {
//        $arr = separateTags($tag);
//
//        if ( count($arr['tag']) ) {
//            return Cache::tags($arr['tag'])->get($arr['key']);
//        } else {
//            return Cache::get($arr['key']);
//        }
//
//    }
//}
//
//if ( !function_exists('flushCache') ) {
//    function flushCache($tag, bool $recursive = false) {
//        $arr = separateTags($tag);
//
//        if ( count($arr['tag']) ) {
//            if ( $recursive ) {
//                return Cache::tags($arr['tag'])->flush();
//            } else {
//                $lastTag = array_pop($arr['tag']);
//                return Cache::tags($lastTag)->flush();
//            }
//        } else {
//            return Cache::forget($arr['key']);
//        }
//
//    }
//}
//
//
//if ( !function_exists('separateTags') ) {
//    function separateTags($tag) {
//        $keyExp = explode('.', $tag);
//        $key = array_pop($keyExp);
//        $keyCnt = count($keyExp);
//        $tagArr = [];
//
//        if ($keyCnt > 1) {
//            for ($i=0; $i<$keyCnt; $i++) {
//                $_tags = [];
//                for ($j=0; $j<=$i; $j++) {
//                    $_tags[] =  $keyExp[$j];
//                }
//                $tagArr[] = implode('.', $_tags);
//            }
//        }
//
//        return ['key' => $key, 'tag' => $tagArr];
//    }
//}



}
