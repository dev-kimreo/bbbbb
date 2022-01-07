<?php

namespace App\Exceptions;

use App\Libraries\CollectionLibrary;
use Exception;

class QpickHttpException extends Exception
{
    protected array $errors;

    public function __construct($httpStatusCode, $errorCode, $targetKey = false)
    {
        $errorInfo = $this->getErrorInfo($errorCode, $targetKey);
        $this->errors[] = $errorInfo;

        parent::__construct($errorInfo['msg'], $httpStatusCode);
    }

    public function getStatusCode()
    {
        return $this->getCode();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function getErrorInfo($code, $key): array
    {
        // Getting Translated Message
        $msg = __('exception.' . $code);

        // Invalid message
        if(!$msg || $msg == $code) {
            $msg = 'No corresponding message for the given code.';
        }

        // Generating Response Data
        if($key) {
            $res = [
                'code' => $code,
                'target' => CollectionLibrary::hasKeyCaseInsensitive(collect(request()->originals), $key),
                'msg' => $msg
            ];
        } else {
            $res = [
                'code' => $code,
                'msg' => $msg
            ];
        }

        // Return
        return $res;
    }
}
