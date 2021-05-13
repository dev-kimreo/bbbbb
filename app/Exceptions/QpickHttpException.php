<?php

namespace App\Exceptions;

use Exception;

class QpickHttpException extends Exception
{
    protected $errors;

    public function __construct($httpStatusCode, $errorCode, $targetKey = false)
    {
        $errorInfo = $this->getErrorInfo($errorCode, $targetKey);
        $this->errors[] = $errorInfo;
        $this->message = $errorInfo['message'];
        $this->code = $httpStatusCode;
    }

    public function getStatusCode()
    {
        return $this->getCode();
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function getErrorInfo($code, ...$key) {
        // Getting Translated Message
        $msg = __(is_integer($code)? config('subError.' . $code): 'messages.' . $code);

        // Invalid message
        if(!$msg || $msg == $code) {
            return [
                'code' => 'unknown',
                'message' => 'There is no matched message.'
            ];
        }

        // Replacing
        preg_match_all("/:{1}[^\s]+[a-z]+/", $msg, $matchArrs);

        foreach ($matchArrs[0] as $k => $attr) {
            if ( is_array($key) && count($key) ) {
                if ( isset($key[$k]) && $key[$k] ) {
                    if (preg_match("/^\{{1}[^\{\}]+\}{1}$/", $key[$k])) {
                        $key[$k] = preg_replace('/\{|\}/', '', $key[$k]);
                        $msg = str_replace($attr, $key[$k], $msg);
                    } else {
                        $msg = str_replace($attr, __('words.' . $key[$k]), $msg);
                    }
                }
            }
        }

        // Generating Response Data
        $res = [
            'code' => $code,
            'target' => $key[0]?? false,
            'message' => $msg
        ];

        if($res['target'] === false) {
            unset($res['target']);
        }

        // Return
        return $res;
    }
}
