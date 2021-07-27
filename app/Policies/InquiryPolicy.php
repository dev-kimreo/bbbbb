<?php

namespace App\Policies;

use App\Models\Users\User;
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

    public function before(User $user, $ability) {
    }

    public function viewAny(User $user)
    {
        return $user->grade != 0;
    }

    public function view(User $user, Inquiry $inquiry)
    {
        return $user->grade != 0 && $user->id === $inquiry->user_id;
    }

    public function create(User $user, Inquiry $inquiry)
    {
        // 준회원이 아닐 경우
        return $user->grade != 0;
    }

    public function update(User $user, Inquiry $inquiry)
    {
        return $user->grade != 0 && $user->id === $inquiry->user_id;
    }

    public function delete(User $user, Inquiry $inquiry)
    {
        return $user->grade != 0 && $user->id === $inquiry->user_id;
    }

}
