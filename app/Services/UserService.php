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
        $privacy = UserPrivacyActive::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        UserPrivacyInactive::query()->insert(
            [
                'user_id' => $user->id,
                'name' => $privacy->name,
                'email' => $privacy->email,
            ]
        );
        $privacy->forceDelete();

        $user->inactivated_at = Carbon::now();
        $user->save();
        return true;
    }

    /**
     * 휴면회원 탈퇴처리 (개인정보 파기)
     */
    static public function permanentWithdrawal(User $user): bool
    {
        $privacy = UserPrivacyInactive::query()
            ->where(['user_id' => $user->id])
            ->firstOrFail();

        UserPrivacyDeleted::query()->insert(
            [
                'user_id' => $user->id,
                'name' => $privacy->name,
                'email' => $privacy->email,
            ]
        );
        $privacy->forceDelete();

        $user->delete();
        return true;
    }
}
