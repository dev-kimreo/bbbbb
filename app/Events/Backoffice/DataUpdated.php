<?php

namespace App\Events\Backoffice;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Model $model;
    public int $id;
    public string $msg;

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
