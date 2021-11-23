<?php

namespace App\Services;

use App\Exceptions\QpickHttpException;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ComponentService
{

    public function __construct()
    {
    }

    /**
     * @throws QpickHttpException
     * 컴포넌트 작성자 확인
     */
    static public function checkRegistrant($component): void
    {
        if (!Auth::user()->can('authorize', $component)) {
            throw new QpickHttpException(403, 'common.forbidden');
        }
    }
}
