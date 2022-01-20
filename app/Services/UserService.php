<?php

namespace App\Services;

use App\Models\Users\User;
use App\Models\Users\UserPrivacyActive;
use App\Models\Users\UserPrivacyDeleted;
use App\Models\Users\UserPrivacyInactive;
use Carbon\Carbon;

class UserService
{
    /**
     * 회원 휴면처리
     */
    static public function inactivate(User $user): bool
    {
        UserPrivacyInactive::query()->create(collect($user->privacy)->put('user_id', $user->id)->toArray());
        $user->privacy->forceDelete();

        $user->inactivated_at = Carbon::now();
        $user->save();

        return true;
    }

    /**
     * 탈퇴처리
     */
    static public function withdrawal(User $user): bool
    {
        $privacyActive = UserPrivacyActive::query()->where(['user_id' => $user->id])->first();
        $privacyInactive = UserPrivacyInactive::query()->where(['user_id' => $user->id])->first();
        $privacy = $privacyActive ?? $privacyInactive;

        UserPrivacyDeleted::query()->create(
            collect($privacy)->put('user_id', $user->id)->toArray()
        );

        $privacy->forceDelete();

        $user->delete();

        return true;
    }

    /**
     * 탈퇴 후 개인정보 보관기간 경과시 파기
     */
    static public function destruct(User $user): bool
    {
        UserPrivacyDeleted::query()->where(['user_id' => $user->id])->forceDelete();
        return true;
    }
}
