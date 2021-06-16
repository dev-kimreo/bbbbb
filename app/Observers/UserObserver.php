<?php

namespace App\Observers;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the Tooltip "created" event.
     *
     * @param User $user
     * @return void
     */
    public function created(User $user)
    {
        DataCreated::dispatch($user, $user->getAttribute('id'), '회원 가입');
    }

    /**
     * Handle the Tooltip "updated" event.
     *
     * @param User $user
     * @return void
     */
    public function updated(User $user)
    {
        DataUpdated::dispatch($user, $user->getAttribute('id'), '회원정보 수정');
    }

    /**
     * Handle the Tooltip "deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function deleted(User $user)
    {
        DataDeleted::dispatch($user, $user->getAttribute('id'), '회원정보 삭제');
    }

    /**
     * Handle the Tooltip "restored" event.
     *
     * @param User $user
     * @return void
     */
    public function restored(User $user)
    {
        DataUpdated::dispatch($user, $user->getAttribute('id'), '삭제된 회원정보 복구');
    }

    /**
     * Handle the Tooltip "force deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        DataDeleted::dispatch($user, $user->getAttribute('id'), '회원정보 영구 삭제');
    }
}
