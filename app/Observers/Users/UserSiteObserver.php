<?php

namespace App\Observers\Users;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\Users\UserSite;

class UserSiteObserver
{
    public function created(UserSite $userSite)
    {
        DataCreated::dispatch($userSite, $userSite->user->getAttribute('id'), '사이트 추가');
    }

    public function updated(UserSite $userSite)
    {
        DataUpdated::dispatch($userSite, $userSite->user->getAttribute('id'), '사이트 정보 수정');
    }

    public function deleted(UserSite $userSite)
    {
        DataDeleted::dispatch($userSite, $userSite->user->getAttribute('id'), '사이트 삭제');
    }

    public function restored(UserSite $userSite)
    {
        DataUpdated::dispatch($userSite, $userSite->user->getAttribute('id'), '삭제된 사이트 복구');
    }

    public function forceDeleted(UserSite $userSite)
    {
        DataDeleted::dispatch($userSite, $userSite->user->getAttribute('id'), '사이트 영구 삭제');
    }
}
