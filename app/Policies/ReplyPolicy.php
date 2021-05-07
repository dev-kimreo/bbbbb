<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Board;
use App\Models\Post;
use App\Models\Reply;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReplyPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function viewAny()
    {

    }

    public function view()
    {

    }

    public function create()
    {
    }

    public function update(User $user, Reply $reply)
    {
        return $user->id === $reply->user_id;
    }

    public function delete(User $user, Reply $reply)
    {
        return $user->id === $reply->user_id;
    }


}
