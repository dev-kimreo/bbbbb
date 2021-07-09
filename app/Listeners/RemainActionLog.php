<?php

namespace App\Listeners;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\ActionLog;
use Auth;
use Illuminate\Http\Request;

class RemainActionLog
{
    protected ActionLog $log;
    protected Request $request;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ActionLog $log, Request $req)
    {
        $this->log = $log;
        $this->request = $req;
    }

    /**
     * Handle the event.
     *
     * @param  DataCreated|DataUpdated|DataDeleted  $event
     * @return void
     */
    public function handle($event)
    {
        if (Auth::hasAccessRightsToBackoffice()) {
            if (method_exists($event->model, 'backofficeLogs')) {
                if ($event instanceof DataUpdated) {
                    // TODO - 백오피스 로그에 변경항목 기록하기
                    //$changes = $event->model->getChanges();
                    $log = $this->log;
                    $log->setAttribute('client_id', Auth::getClientId() ?? 0);
                    $log->setAttribute('user_id', Auth::id() ?? $event->model->user_id);
                    $log->setAttribute('loggable_type', $event->model->getMorphClass());
                    $log->setAttribute('loggable_id', $event->id);
                    $log->setAttribute('crud', $event::$crud);
                    $log->setAttribute('path', $this->request->path());
                    $log->setAttribute('memo', $event->msg);
                    $log->save();
                } elseif ($event instanceof DataCreated || $event instanceof DataDeleted) {
                    $log = $this->log;
                    $log->setAttribute('client_id', Auth::getClientId() ?? 0);
                    $log->setAttribute('user_id', Auth::id() ?? $event->model->id);
                    $log->setAttribute('loggable_type', $event->model->getMorphClass());
                    $log->setAttribute('loggable_id', $event->id);
                    $log->setAttribute('crud', $event::$crud);
                    $log->setAttribute('path', $this->request->path());
                    $log->setAttribute('memo', $event->msg);
                    $log->save();
                }
            }
        }
    }
}
