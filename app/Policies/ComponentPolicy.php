<?php

namespace App\Policies;

use App\Models\Components\Component;
use App\Models\Users\User;
use Auth;
use Illuminate\Auth\Access\HandlesAuthorization;
use Gate;

class ComponentPolicy
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

    public function authorize(User $user, Component $component): bool
    {
        return $component->getAttribute('user_partner_id') == $user->partner->id;
//        $viewAnyPolicy = Gate::allows('viewAny', [$post, $board]);
//        if (Auth::hasAccessRightsToPartner()) {
//            return true;
//        } else {
//            return false;
//        }
    }


}
