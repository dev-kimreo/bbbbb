<?php

namespace App\Observers\Users;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\Users\User;
use App\Models\Users\UserSolution;

class UserSolutionObserver
{
    /**
     * Handle the User solution "created" event.
     *
     * @param UserSolution $solution
     * @return void
     */
    public function created(UserSolution $solution)
    {
        $event = new DataCreated($solution, $solution->getAttribute('id'), '연동 완료');
        $event->setData('solution', $solution->getAttribute('solution'));
        event($event);
    }

    /**
     * Handle the User solution "updated" event.
     *
     * @param UserSolution $solution
     * @return void
     */
    public function updated(UserSolution $solution)
    {
        $event = new DataUpdated($solution, $solution->getAttribute('id'), '연동정보 수정');
        $event->setData('solution', $solution->getAttribute('solution'));
        event($event);
    }

    /**
     * Handle the User solution "deleted" event.
     *
     * @param UserSolution $solution
     * @return void
     */
    public function deleted(UserSolution $solution)
    {
        $event = new DataDeleted($solution, $solution->getAttribute('id'), '연동 해제');
        $event->setData('solution', $solution->getAttribute('solution'));
        event($event);
    }
}
