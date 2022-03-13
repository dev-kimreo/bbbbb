<?php

namespace App\Observers\Users;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\Users\UserAdvAgree;

class UserAdvAgreeObserver
{
    /**
     * Handle the Tooltip "created" event.
     *
     * @param UserAdvAgree $userAdvAgree
     * @return void
     */
    public function created(UserAdvAgree $userAdvAgree)
    {
        DataCreated::dispatch($userAdvAgree->user()->getModel(), $userAdvAgree->user()->first()->getAttribute('id'), '광고수신동의 기입(' . ($userAdvAgree->getAttribute('agree') ? '동의' : '미동의')  . ')');
    }

    /**
     * Handle the Tooltip "updated" event.
     *
     * @param UserAdvAgree $userAdvAgree
     * @return void
     */
    public function updated(UserAdvAgree $userAdvAgree)
    {
        DataUpdated::dispatch($userAdvAgree->user()->getModel(), $userAdvAgree->user()->first()->getAttribute('id'), '광고수신동의 수정');
    }

    /**
     * Handle the Tooltip "deleted" event.
     *
     * @param UserAdvAgree $userAdvAgree
     * @return void
     */
    public function deleted(UserAdvAgree $userAdvAgree)
    {
        DataDeleted::dispatch($userAdvAgree->user()->getModel(), $userAdvAgree->user()->first()->getAttribute('id'), '광고수신동의 삭제');
    }

    /**
     * Handle the Tooltip "restored" event.
     *
     * @param UserAdvAgree $userAdvAgree
     * @return void
     */
    public function restored(UserAdvAgree $userAdvAgree)
    {
        DataUpdated::dispatch($userAdvAgree->user()->getModel(), $userAdvAgree->user()->first()->getAttribute('id'), '삭제된 광고수신동의 복구');
    }

    /**
     * Handle the Tooltip "force deleted" event.
     *
     * @param UserAdvAgree $userAdvAgree
     * @return void
     */
    public function forceDeleted(UserAdvAgree $userAdvAgree)
    {
        DataDeleted::dispatch($userAdvAgree->user()->getModel(), $userAdvAgree->user()->first()->getAttribute('id'), '광고수신동의 영구 삭제');
    }
}
