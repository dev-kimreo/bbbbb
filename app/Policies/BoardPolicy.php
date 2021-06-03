<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Board;
use Illuminate\Auth\Access\HandlesAuthorization;

class BoardPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function before(User $user, $ability)
    {
    }

    public function viewAny(?User $user)
    {
        if ($user->hasAccessRightsToBackoffice()) {
            return true;
        } else {
            return true;
        }
    }

    public function view(?User $user, Board $board)
    {
        if ($user->hasAccessRightsToBackoffice()) {
            return true;
        } else {
            return $board->enable ? true : false;
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user, Board $board)
    {
        if ($user->hasAccessRightsToBackoffice()) {
            return true;
        } else {
            return false;
        }
    }

    public function update(User $user)
    {

    }

    public function delete(User $user)
    {

    }

    public function checkUsableReply(User $user, Board $board)
    {
        return $board->options['reply'] ? true : false;
    }

}
