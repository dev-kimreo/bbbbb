<?php

namespace App\Listeners;

use App\Events\Backoffice\DataChanged;
use App\Models\BackofficeLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

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
     * @param  DataChanged  $event
     * @return void
     */
    public function handle(DataChanged $event)
    {
        if (method_exists($event->model, 'backofficeLogs')) {
            $log = $this->log;
            $log->setAttribute('user_id', Auth::id());
            $log->setAttribute('loggable_type', $event->model->getMorphClass());
            $log->setAttribute('loggable_id', $event->id);
            $log->setAttribute('memo', $event->msg);
            $log->save();
        }
    }
}
