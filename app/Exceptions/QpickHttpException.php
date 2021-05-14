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

    protected function getErrorInfo($code, $key) {
        // Getting Translated Message
        $msg = __('messages.' . $code);

        // Invalid message
        if(!$msg || $msg == $code) {
            $msg = 'No corresponding message for the given code.';
        }

        // Generating Response Data
        if($key) {
            $res = [
                'code' => $code,
                'target' => $key ?? false,
                'message' => $msg
            ];
        } else {
            $res = [
                'code' => $code,
                'message' => $msg
            ];
        }

        // Return
        return $res;
    }
}
