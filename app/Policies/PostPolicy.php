<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Board;
use App\Models\Post;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
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

    public function create(User $user, Post $post, Board $board)
    {
        if ($board->options['board'] == 'manager') {
            if ($user->checkAdmin()) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function update(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }

    public function isHidden(User $user, Post $post)
    {
        return $post->hidden ? true : false;
    }


}
