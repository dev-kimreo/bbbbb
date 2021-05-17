<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Inquiry;
use Illuminate\Auth\Access\HandlesAuthorization;

class InquiryPolicy
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

    public function before(User $user) {
        // 준회원이 아닐 경우
        return $user->grade != 0;
    }

    public function viewAny()
    {
    }

    public function view(User $user, Inquiry $inquiry)
    {
        return $user->id === $inquiry->user_id;
    }

    public function create(User $user, Inquiry $inquiry)
    {
    }

    public function update(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }

}
