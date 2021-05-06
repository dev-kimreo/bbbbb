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
        //
    }

    /**
     * @param User $user
     * @param Board $board
     * @return bool
     */
    public function create(User $user, Board $board) {
        if ($board->options['board'] == 'manager') {
            if ($user->grade != 100) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
}
