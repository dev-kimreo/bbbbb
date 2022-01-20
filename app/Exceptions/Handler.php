<?php

namespace App\Exceptions;

use App\Libraries\CollectionLibrary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;
use Error;
use Str;
use ErrorException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        $effectedExceptionEntity = null;

        // Define the response
        $response = [
            'errors' => []
        ];

        // If the app is in debug mode
        if (config('app.debug')) {
            // Add the exception class name, message and stack trace to response
            $response['debug'] = [
                'class' => get_class($e),
                'trace' => $e->getTrace()
            ];
        }

        // Grab the HTTP status code and message from the Exception
        if ($this->isHttpException($e) && method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
            $response['errors'][] = [
                'code' => 'system.http.' . $statusCode,
                'msg' => $e->getMessage()
            ];
        } elseif ($e instanceof QpickHttpException) {
            $statusCode = $e->getStatusCode();
            $response['errors'] = $e->getErrors();
        } elseif ($e instanceof ErrorException || $e instanceof Error) {
            $statusCode = 500;
            $response['errors'][] = [
                'code' => 'system.internalError',
                'msg' => $e->getMessage()
            ];
        } elseif ($e instanceof ModelNotFoundException) {
            $currentModel = '\\' . $e->getModel();
            $effectedExceptionEntity = $currentModel::$exceptionEntity ?? null;

            $statusCode = 404;
            $response['errors'][] = [
                'code' => 'system.http.' . $statusCode,
                'msg' => $e->getMessage()
            ];
        } elseif ($e instanceof ValidationException) {
            $statusCode = 422;
            $rules = $e->validator->failed();

            foreach ($e->errors() as $field => $v) {
                $rule = array_keys($rules[$field]);

                if (is_array($rule)) {
                    foreach ($rule as $k => $code) {
                        if (strpos($code, '\\') !== false) {
                            $code = substr(strrchr($code, '\\'), 1);
                        }

                        $rule[$k] = Str::snake($code);
                    }
                }

                foreach ($v as $k => $message) {
                    $response['errors'][] = [
                        'code' => 'validation.' . lcfirst($rule[$k]),
                        'target' => CollectionLibrary::hasKeyCaseInsensitive(collect(request()->originals), $field),
                        'msg' => $message
                    ];
                }
            }
        } else if ($e instanceof RouteNotFoundException) {
            $statusCode = 404;
            $response['errors'][] = [
                'code' => 'system.http.' . $statusCode,
                'msg' => $e->getMessage()
            ];
        } elseif ($e instanceof QueryException) {

            // duplicate
            if (Str::contains($e->getMessage(), 'Duplicate entry')) {
                $statusCode = 409;
                $response['errors'][] = [
                    'code' => 'system.databaseError',
                    'msg' => 'Duplicate Entry resource'
                ];
            } else {
                $statusCode = 500;
                $response['errors'][] = [
                    'code' => 'system.databaseError',
                    'msg' => $e->getMessage()
                ];
            }

        } else {
            $o = parent::render($request, $e);
            $statusCode = $o->getStatusCode();
            $response['errors'][] = [
                'code' => 'system.http.' . $statusCode,
                'msg' => method_exists($e, 'getMessage') ? $e->getMessage() : $o->original['msg']
            ];
        }

        //
        if (Route::getCurrentRoute()) {
            $exceptionPrefix = 'exception';
            $effectedExceptionEntity = $effectedExceptionEntity ?? (Route::getCurrentRoute()->getController()->exceptionEntity ?? '');

            foreach ($response['errors'] as $k => &$arr) {
                $changed = [];

                if (isset($arr['target'])) {
                    $currentAttr = $effectedExceptionEntity . '.' . $arr['target'];
                } else {
                    $currentAttr = $effectedExceptionEntity;
                }

                $arr['user_code'] = $arr['code'] . '.' . ($currentAttr);
                $arr['user_msg'] = __($exceptionPrefix . '.' . $arr['user_code']);

                if (!$arr['user_msg'] || $arr['user_msg'] == $exceptionPrefix . '.' . $arr['user_code']) {
                    $arr['user_msg'] = $arr['msg'];
                } else {
                    $arr['user_msg'] = __($exceptionPrefix . '.' . $arr['user_code']);
                }
            }
        }

        // Default Renderder
        return response()->json($response, $statusCode);
    }
}
