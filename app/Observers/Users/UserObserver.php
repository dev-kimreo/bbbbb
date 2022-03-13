<?php

namespace App\Observers\Users;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\Users\User;
use Carbon\Carbon;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param User $user
     * @return void
     */
    public function created(User $user)
    {
        DataCreated::dispatch($user, $user->getAttribute('id'), '회원가입');
    }

    /**
     * Handle the User "saving" event.
     *
     * @param User $user
     * @return void
     */
    public function saving(User $user)
    {
        if ($user->isDirty('password')) {
            $user->setAttribute('last_password_changed_at', Carbon::now());
        }
    }

    /**
     * Handle the User "updated" event.
     *
     * @param User $user
     * @return void
     */
    public function updated(User $user)
    {
        DataUpdated::dispatch($user, $user->getAttribute('id'), '회원정보 수정');

        $changedColumns = $user->getChanges();

        if (isset($changedColumns['name'])) {
            DataUpdated::dispatch($user, $user->getAttribute('id'), '이름 변경');
        }

        if (isset($changedColumns['password'])) {
            DataUpdated::dispatch($user, $user->getAttribute('id'), '비밀번호 변경');
        }
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function deleted(User $user)
    {
        DataDeleted::dispatch($user, $user->getAttribute('id'), '회원탈퇴');
    }

    /**
     * Handle the Tooltip "restored" event.
     *
     * @param User $user
     * @return void
     */
    public function restored(User $user)
    {
        DataUpdated::dispatch($user, $user->getAttribute('id'), '탈퇴회원 복구');
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        DataDeleted::dispatch($user, $user->getAttribute('id'), '회원탈퇴');
    }
}
