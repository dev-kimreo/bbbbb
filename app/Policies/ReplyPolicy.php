<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Board;
use App\Models\Post;
use App\Models\Reply;
use Auth;
use Illuminate\Auth\Access\HandlesAuthorization;
use Gate;

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

    public function viewAny(?User $user, Reply $reply, Post $post, Board $board)
    {
        return Gate::allows('view', [$post, $board]);
    }

    public function view()
    {

    }

    public function create(User $user, Reply $reply, Post $post, Board $board)
    {
        if (Auth::hasAccessRightsToBackoffice()) {
            return true;
        } else {
//            // Post 의 볼 권한이 있는지 체크
//            if (Gate::allows('view', [$post, $board])) {
//                return true;
//            } else {
            return false;
//            }
        }
    }

    public function update(User $user, Reply $reply)
    {
        if (Auth::hasAccessRightsToBackoffice()) {
            return $user->id === $reply->user_id;
        } else {
            return false;
//            if (Auth::user()->can('viewAny', [$reply, $reply->post, $reply->post->board])) {
//
//            }
        }
    }

    public function delete(User $user, Reply $reply)
    {
        if (Auth::hasAccessRightsToBackoffice()) {
            return $user->id === $reply->user_id;
        } else {
            return false;
//            if (Auth::user()->can('viewAny', [$reply, $reply->post, $reply->post->board])) {
//
//            }
        }
    }


}
