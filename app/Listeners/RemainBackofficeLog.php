<?php

namespace App\Listeners;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\BackofficeLog;
use Auth;

class RemainBackofficeLog
{
    protected $log;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(BackofficeLog $log)
    {
        $this->log = $log;
    }

    /**
     * Handle the event.
     *
     * @param  DataCreated|DataUpdated|DataDeleted  $event
     * @return void
     */
    public function handle($event)
    {
        if (method_exists($event->model, 'backofficeLogs')) {
            if ($event instanceof DataUpdated) {
                // TODO - 백오피스 로그에 변경항목 기록하기
                //$changes = $event->model->getChanges();
                $log = $this->log;
                $log->setAttribute('user_id', Auth::id());
                $log->setAttribute('loggable_type', $event->model->getMorphClass());
                $log->setAttribute('loggable_id', $event->id);
                $log->setAttribute('memo', $event->msg);
                $log->save();
            } elseif ($event instanceof DataCreated || $event instanceof DataDeleted) {
                $log = $this->log;
                $log->setAttribute('user_id', Auth::id());
                $log->setAttribute('loggable_type', $event->model->getMorphClass());
                $log->setAttribute('loggable_id', $event->id);
                $log->setAttribute('memo', $event->msg);
                $log->save();
            }
        }
    }
}
