<?php

namespace App\Observers;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\Users\User;
use App\Models\Users\UserSite;

class UserSiteObserver
{
    /**
     * Handle the User site "created" event.
     *
     * @param UserSite $site
     * @return void
     */
    public function created(UserSite $site)
    {
        $event = new DataCreated($site, $site->getAttribute('id'), '연동 완료');
        $event->setData('solution', $site->getAttribute('solution'));
        event($event);
    }

    /**
     * Handle the User site "updated" event.
     *
     * @param UserSite $site
     * @return void
     */
    public function updated(UserSite $site)
    {
        $event = new DataUpdated($site, $site->getAttribute('id'), '연동정보 수정');
        $event->setData('solution', $site->getAttribute('solution'));
        event($event);
    }

    /**
     * Handle the User site "deleted" event.
     *
     * @param UserSite $site
     * @return void
     */
    public function deleted(UserSite $site)
    {
        $event = new DataDeleted($site, $site->getAttribute('id'), '연동 해제');
        $event->setData('solution', $site->getAttribute('solution'));
        event($event);
    }
}
