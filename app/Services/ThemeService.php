<?php

namespace App\Services;

use App\Exceptions\QpickHttpException;
use App\Models\Themes\Theme;
use Auth;

class ThemeService
{
    public function __construct()
    {

    }

    public function usableAuthor(Theme $theme)
    {
        // check policy
        return Auth::user()->can('authorize', $theme);
    }
}
