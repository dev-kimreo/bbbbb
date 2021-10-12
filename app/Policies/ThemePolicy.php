<?php

namespace App\Policies;

use App\Models\Themes\ThemeProduct;
use App\Models\Users\User;
use App\Models\Themes\Theme;
use Auth;
use Illuminate\Auth\Access\HandlesAuthorization;
use Gate;

class ThemePolicy
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

    public function authorize(User $user, Theme $theme): bool
    {
        $themeProduct = ThemeProduct::select('user_partner_id')->find($theme->getAttribute('theme_product_id'));

        return $themeProduct->getAttribute('user_partner_id') == $user->partner->id;
//        $viewAnyPolicy = Gate::allows('viewAny', [$post, $board]);
//        if (Auth::hasAccessRightsToPartner()) {
//            return true;
//        } else {
//            return false;
//        }
    }


}
