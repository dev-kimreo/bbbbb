<?php

namespace App\Events\Backoffice;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $model;
    public $id;
    public $msg;

    /**
     * Create a new event instance.
     *
     * @param Model $model
     * @param int $id
     * @param string $msg
     */
    public function __construct(Model $model, int $id, string $msg)
    {
        $this->model = $model;
        $this->id = $id;
        $this->msg = $msg;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
