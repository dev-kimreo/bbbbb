<?php

namespace App\Events\Member;

use App\Models\Users\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class Logout
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Model $model;
    public int $id;
    public int $user_id;
    public int $user_grade;
    public ?int $manager_id = null;
    public int $client_id;
    public ?string $ip;
    public string $title;
    public ?array $properties = [];

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Request $req, int $user_id, int $user_grade, int $client_id, int $manager_id = null)
    {
        $this->model = User::find($user_id);
        $this->id = $user_id;
        $this->user_id = $user_id;
        $this->user_grade = $user_grade;
        $this->manager_id = $manager_id;
        $this->client_id = $client_id;
        $this->ip = $req->ip();
        $this->title = '로그아웃';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
