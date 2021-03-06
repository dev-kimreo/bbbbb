<?php

namespace App\Policies;

use App\Models\Users\User;
use App\Models\Attach\AttachFile;
use Auth;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttachFilePolicy
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

    public function update(User $user, AttachFile $attachFile)
    {
        return $user->id === $attachFile->user_id || Auth::isLoggedForBackoffice();
    }

    public function delete(User $user, AttachFile $attachFile)
    {
        return $user->id === $attachFile->user_id || Auth::isLoggedForBackoffice();
    }


}
