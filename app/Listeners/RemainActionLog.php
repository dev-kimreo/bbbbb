<?php

namespace App\Listeners;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Events\Member\Login;
use App\Models\ActionLog;
use Auth;
use Illuminate\Database\Eloquent\Model;
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
     * @param  DataCreated|DataUpdated|DataDeleted|Login  $event
     * @return void
     */
    public function handle($event)
    {
        $log = $this->log;

        if ($event instanceof Login) {
            $log->setAttribute('client_id', $event->client_id);
            $log->setAttribute('user_id', $event->manager_id ?? $event->user_id);
            $log->setAttribute('loggable_type', 'user');
            $log->setAttribute('loggable_id', $event->user_id);
            $log->setAttribute('title', $event->manager_id? '관리자 로그인': '로그인');
        } elseif (
            method_exists($event->model, 'actionLogs') ||
            (method_exists($event->model, 'backofficeLogs') && Auth::hasAccessRightsToBackoffice())
        ) {
            $log->setAttribute('client_id', Auth::getClientId() ?? 0);
            $log->setAttribute('user_id', Auth::id() ?? $event->model->getAttribute('user_id'));
            $log->setAttribute('loggable_type', $event->model->getMorphClass());
            $log->setAttribute('loggable_id', $event->id);
            $log->setAttribute('title', $event->title);
        } else {
            return;
        }

        $log->setAttribute('ip', $this->request->ip());
        $log->setAttribute('crud', $event::$crud ?? 'r');
        $log->setAttribute('path', $this->request->path());
        $log->setAttribute('memo', $event->memo ?? null);
        $log->setAttribute('properties', $event->properties && $event->properties->count()? $event->properties->toJson(): '{}');
        $log->save();
    }

    public function customLog(Model $model, string $title, ?string $memo = null)
    {
        $log = $this->log;
        $log->setAttribute('client_id', Auth::getClientId() ?? 0);
        $log->setAttribute('user_id', Auth::id() ?? $model->getAttribute('user_id'));
        $log->setAttribute('ip', $this->request->ip());
        $log->setAttribute('loggable_type', $model->getMorphClass());
        $log->setAttribute('loggable_id', $model->getAttribute('id'));
        $log->setAttribute('path', $this->request->path());
        $log->setAttribute('title', $title);
        $log->setAttribute('memo', $memo ?? null);
        $log->setAttribute('properties', '{}');
        $log->save();
    }
}
