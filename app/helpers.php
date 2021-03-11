<?php

//use Illuminate\Support\Facades\Auth;

if (!function_exists('getResponseError'))
{
    function getResponseError($code)
    {
        $errCfg = config('subError.' . $code);

        if (isset($errCfg)) {
            return json_encode([
                'code' => $code,
                'message' => __($errCfg)
            ]);
        } else {
            return false;
        }
    }
}
