<?php

namespace App\Policies;

use App\Models\User;
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
        if ($user->checkAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user)
    {

    }

    public function view(User $user)
    {

    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
    }

    public function update(User $user)
    {

    }

    public function delete(User $user)
    {

    }

}
