<?php
namespace App\Libraries\Facades;

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
        if (!self::check() || !self::user()) {
            return false;
        }

        $user = self::user();
        return $user->manager && $user->access()->client_id == 2;
    }
}