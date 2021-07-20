<?php

namespace App\Listeners;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Events\Member\Login;
use App\Events\Member\Logout;
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
     * @param  DataCreated|DataUpdated|DataDeleted|Login|Logout  $event
     * @return void
     */
    public function handle($event)
    {
        // init
        $model = $event->model;
        $id = $event->id;
        $log = $this->log;
        $conn_id = Auth::getConnectId();

        // get the origin log
        $where = [
            'loggable_type' => $model->getMorphClass(),
            'loggable_id' => $id,
            'conn_id' => $conn_id,
            'path' => $this->request->path()
        ];

        if ($origin = ActionLog::where($where)->first()) {
            $log = $origin;
        }

        // get some attribute values
        if ($event instanceof Login || $event instanceof Logout) {
            $client_id = $event->client_id;
            $user_id = $event->manager_id ?? $event->user_id;
        } elseif (
            method_exists($model, 'actionLogs') ||
            (method_exists($model, 'backofficeLogs') && Auth::hasAccessRightsToBackoffice())
        ) {
            $client_id = Auth::getClientId() ?? 0;
            $user_id = Auth::id() ?? $model->getAttribute('user_id');
        } else {
            return;
        }

        // set attributes
        $log->setAttribute('conn_id', $conn_id);
        $log->setAttribute('client_id', $client_id);
        $log->setAttribute('user_id', $user_id);
        $log->setAttribute('loggable_type', $model->getMorphClass());
        $log->setAttribute('loggable_id', $id);
        $log->setAttribute('title', $event->title);
        $log->setAttribute('ip', $this->request->ip());
        $log->setAttribute('crud', $event::$crud ?? 'r');
        $log->setAttribute('path', $this->request->path());
        $log->setAttribute('memo', $event->memo ?? null);

        // set properties
        $properties = collect($origin->properties ?? []);
        $properties = $properties->mergeRecursive($event->properties ?? []);
        $properties = $properties->replace([
            'changes' => collect($properties->get('changes'))->unique()->toArray()
        ]);
        $log->setAttribute('properties', $properties);

        // save
        $log->save();
    }

    public function customLog(Model $model, string $title, ?string $memo = null)
    {
        $log = $this->log;
        $log->setAttribute('conn_id', Auth::getConnectId());
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
