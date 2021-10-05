<?php

namespace App\Policies;

use App\Models\Users\User;
use App\Models\Themes\ThemeProduct;
use Auth;
use Illuminate\Auth\Access\HandlesAuthorization;
use Gate;

class ThemeProductPolicy
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

    public function create(User $user, ThemeProduct $themeProduct): bool
    {
        if (Auth::hasAccessRightsToPartner()) {
            return true;
        } else {
            return false;
        }
    }

    public function update(User $user, ThemeProduct $themeProduct): bool
    {
        if (Auth::hasAccessRightsToPartner()) {
            return $user->partner->id === $themeProduct->user_partner_id;
        } else {
            return false;
        }
    }

    public function delete(User $user, ThemeProduct $themeProduct): bool
    {
        if (Auth::hasAccessRightsToPartner()) {
            return $user->partner->id === $themeProduct->user_partner_id;
        } else {
            return false;
        }
    }


}
