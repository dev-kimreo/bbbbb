<?php

namespace App\Events\Member;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class Login
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $user_id;
    public ?int $manager_id = null;
    public int $client_id;
    public ?string $ip;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Request $req, int $user_id, int $client_id, int $manager_id = null)
    {
        $this->user_id = $user_id;
        $this->manager_id = $manager_id;
        $this->client_id = $client_id;
        $this->ip = $req->ip();
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