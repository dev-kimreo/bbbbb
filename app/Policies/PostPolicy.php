<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Board;
use App\Models\Post;
use Illuminate\Auth\Access\HandlesAuthorization;
use Gate;

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

    public function viewAny(?User $user, Post $post, Board $board)
    {
        // Todo Post viewAny Policy 에서 Board View Policy 참조
        return Gate::allows('view', [$board]);
    }

    public function view(?User $user, Post $post, Board $board)
    {
        // Todo Post view Policy 에서 Post viewAny Policy 참조
        $viewAnyPolicy = Gate::allows('viewAny', [$post, $board]);

        if ($viewAnyPolicy) {
            return $post->hidden ? false : true;
        }

        return false;
    }

    public function create(User $user, Post $post, Board $board)
    {
        if ($user->hasAccessRightsToBackoffice()) {
            return true;
        } else {
//            if ($board->options['board'] != 'manager') {
//                return $board->enable ? true : false;
//            } else {
                return false;
//            }
        }
    }

    public function update(User $user, Post $post)
    {
        if ($user->hasAccessRightsToBackoffice()) {
            return $user->id === $post->user_id;
        } else {
            return false;
        }
    }

    public function delete(User $user, Post $post)
    {
        if ($user->hasAccessRightsToBackoffice()) {
            return $user->id === $post->user_id;
        } else {
            return false;
        }
    }

    public function isHidden(User $user, Post $post)
    {
        return $post->hidden ? true : false;
    }


}
