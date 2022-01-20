<?php

namespace App\Libraries;

use App\Exceptions\QpickHttpException;

class PaginationLibrary {
//    private static $cacheCls = null;
//    public static $tags = null;
//    public static $tagArr = [];
    private static $pageCls = null;
    public static $page = 1;
    public static $skip = 0;
    public static $perPage = 15;
    public static $block = 1;
    public static $perBlock = 10;
    public static $totalCount = 0;
    public static $totalPage = 0;
    public static $totalBlock = 0;
    public static $startPage = 0;
    public static $endPage = 0;
    public static $prev = [];
    public static $next = [];

    public function __construct() {
    }

    public static function init() {
        if (is_null(self::$pageCls)) {
            self::$pageCls = new PaginationLibrary();
        }
    }

    public static function set($page, $totalCount = 0, $perPage = 0, $perBlock = 0){
        self::init();

        self::$page = isset($page) && intval($page) > 0 ? intval($page) : self::$page;
        self::$perPage = isset($perPage) && intval($perPage) ? intval($perPage) : self::$perPage;
        self::$skip =  (self::$page - 1) * self::$perPage;
        self::$perBlock = isset($perBlock) && intval($perBlock) ? intval($perBlock) : self::$perBlock;
        self::$totalCount = intval($totalCount);
        self::$totalPage = self::$totalCount > 0 ? ceil(self::$totalCount / self::$perPage) : 1;
        self::$totalBlock = ceil(self::$totalPage / self::$perBlock);

        return self::check();
    }

    public static function check(){

        // 현재 페이지가 총 페이지보다 클 경우
        if ( self::$page > self::$totalPage ) {
            throw new QpickHttpException(404, 'common.pagination.out_of_bounds');
        }

        self::$block = ceil(self::$page / self::$perBlock);
        self::$startPage = (self::$block-1) * self::$perBlock + 1;
        self::$endPage = min(self::$block * self::$perBlock, self::$totalPage);
        self::$prev = [];
        self::$next = [];
        if ( (self::$block - 2) >= 0 ) {
            self::$prev['start'] = (self::$block-2) * self::$perBlock + 1;
            self::$prev['end'] = (self::$block-1) * self::$perBlock;
        }

        if ( (self::$block + 1) <= self::$totalBlock ) {
            self::$next['start'] = (self::$block * self::$perBlock) + 1;
            self::$next['end'] = (self::$block + 1) * self::$perBlock;
        }

        $res = [
            'page' => self::$page,
            'perPage' => self::$perPage,
            'skip' => self::$skip,
            'block' => self::$block,
            'perBlock' => self::$perBlock,
            'totalCount' => self::$totalCount,
            'totalPage' => self::$totalPage,
            'totalBlock' => self::$totalBlock,
            'startPage' => self::$startPage,
            'endPage' => self::$endPage,
        ];

        if ( isset(self::$prev['start']) ) {
            $res['prev'] = self::$prev;
        }

        if ( isset(self::$next['start']) ) {
            $res['next'] = self::$next;
        }

        return $res;
    }




}
