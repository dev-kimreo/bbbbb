<?php

namespace App\Libraries\Facades;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class QpickAuth extends Auth
{
    /**
     * 현재 로그인된 사용자가 백오피스 접근권한을 보유하고 있는지 확인
     *
     * @return bool
     */
    public static function hasAccessRightsToBackoffice(): bool
    {
        return self::isLoggedForBackoffice()
            && self::user()->manager;
    }

    /**
     * 현재 로그인된 사용자가 정회원 사용가능 API의 접근권한을 보유하고 있는지 확인
     *
     * @return bool
     */
    public static function hasAccessRightsToFrontForRegular(): bool
    {
        return self::isLoggedForFront()
            && self::user()->getAttribute('grade') == 1;
    }

    /**
     * 현재 사용자가 프론트 화면용으로 로그인되어 있는지 확인
     *
     * @return bool
     */
    public static function isLoggedForFront(): bool
    {
        return self::check()
            && self::user()
            && self::user()->token()->getAttribute('client_id') == 1;
    }

    /**
     * 현재 사용자가 백오피스 화면용으로 로그인되어 있는지 확인
     *
     * @return bool
     */
    public static function isLoggedForBackoffice(): bool
    {
        return self::check()
            && self::user()
            && self::user()->token()->getAttribute('client_id') == 2;
    }

    /**
     * 현재 로그인된 사용자의 id가 주어진 인자와 동일한지 확인
     *
     * @param int $user_id
     * @return bool
     */
    public static function isSameUserAs(int $user_id): bool
    {
        return self::check()
            && self::id() == $user_id;
    }

    public static function user(): ?Authenticatable
    {
        static $res;
        if (!is_object($res)) {
            $res = parent::user();
            $privacy = $res->privacy()->first();
            $res->name = $privacy->name;
            $res->email = $privacy->email;
        }
        return $res;
    }
}
