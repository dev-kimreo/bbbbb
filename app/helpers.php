<?php

//use Illuminate\Support\Facades\Auth;

if (!function_exists('getErrorCode')) {
    function getErrorCode($code) {
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

if (!function_exists('getResponseError')) {
    function getResponseError($code, $key = '') {
        $err = json_decode(getErrorCode($code), true);

        if ($err) {
            $res = [];
            $res[$code] = [];
            if (!empty($key)) {
                $res[$code]['key'] = $key;
            }
            $res[$code]['message'] = $err['message'];

            return makeResponseErrors([
                'codes' => $res
            ]);
        } else {
            return false;
        }
    }
}

if (!function_exists('getValidationErrToArr')) {
    function getValidationErrToArr($errs) {
        $errors = $errs->toArray();

        $resErr = array();

        foreach ($errors as $key => $err) {
            $msg = array_shift($err);
            $errArrs = json_decode($msg, true);

            if (is_array($errArrs)) {
                $resErr['codes'][$errArrs['code']] = array(
                    'key' => $key,
                    'message' => $errArrs['message']
                );
            } else {
                $resErr['basic'][$key] = $msg;
            }
        }

        return makeResponseErrors($resErr);
    }
}

if (!function_exists('makeResponseErrors')) {
    function makeResponseErrors($errs) {
        $res = [];

        if (isset($errs['basic'])) {
            $res = $errs['basic'];
        }

        if (isset($errs['codes'])) {
            $res['statusCode'] = $errs['codes'];
        }

        return ['errors' => $res];
    }
}


if (!function_exists('checkPwdPattern')) {
    function checkPwdPattern (string $str) {

        $res = array();

        /**
         * 체크
        */
        $combiPattern = "/^(?=.*[a-zA-Z])(?=.*[!\`\~\@\#\$\%\^\&\*\(\)\-\_\=\+])(?=.*[0-9]).{8,25}$/";  // 알파벳, 특문, 숫자 3가지 조합 및 길이 체크 패턴

        $combinationFlag = preg_match($combiPattern, $str) ? true : false;  // 알파벳, 특문, 숫자 3가지 조합 체크 true 통과 false 미통과
        $conFlag = true;    // 연속적 문자, 동일한 문자 체크 true 통과 false 미통과
        $emptyFlag = preg_match("/[\s]/", $str) ? false : true ; // 공백 문자 통과 여부 true 통과 false 미통과

        /**
         * 연속된 문자 체크
         */
        $o = $d = $p = $n = 0;
        $l = 4; // 연속된 문자 체크 길이

        for ($i = 0; $i < strlen($str); $i++) {
            $c = ord($str[$i]);

            if ($i > 0 && ($p = $o - $c) > -2 && $p < 2 && ($n = $p == $d ? $n + 1 : 0) > $l - 3) {
                $conFlag = false;
                break;
            }

            $d = $p;
            $o = $c;
        }

        /**
         * 결과 반환
         */
        $res['combination'] = $combinationFlag;
        $res['continue'] = $conFlag;
        $res['empty'] = $emptyFlag;

        return $res;
    }
}

if (!function_exists('checkPwdSameId')) {
    function checkPwdSameId($pwd, $email) {
        $id = explode('@', $email)[0];
        $pwdLen = strlen($pwd);
        for ($i=0; $i<$pwdLen-3; $i++) {
            if (strpos($id, substr($pwd, $i, 4)) !== false ) {
                return false;
            }
        }
        return true;
    }
}
