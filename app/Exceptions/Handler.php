<?php

namespace App\Exceptions;

use AppLog;
use App\Services\Traits\ResponseCodeTrait;
// use Exception;
use Throwable;
use App\Exceptions\KisshtErrorException;
use App\Exceptions\ServiceCallFailedException;
use App\Exceptions\ValidationFailedException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;


/**
 * Class Handler extends ExceptionHandler
 * @package App\Exceptions
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class Handler extends ExceptionHandler
{
    use ResponseCodeTrait;

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
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Exception $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        // if ($this->shouldReport($exception)) {
        //     Bugsnag::notifyException($exception);
        // }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        $response = [];
        $response['request_id'] = AppLog::getRequestId();
        $response['success'] = false;
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {

            $response['response_code'] = 104;
            $response['message'] = "No record found.";
            return response()->json($response, 404);
        } elseif ($exception instanceof ServiceCallFailedException) {

            $response['response_code'] = 102;
            $response['message'] = $exception->getMessage();
            return response()->json($response, 502);
        } elseif ($exception instanceof ExternalCallFailedException) {

            $response['response_code'] = 102;
            $response['message'] = $exception->getMessage();
            return response()->json($response, 500);
        } elseif ($exception instanceof ValidationFailedException) {

            $response['response_code'] = 101;
            $response['message'] = $exception->getMessage();
            return response()->json($response, 400);
        } elseif ($exception instanceof KisshtErrorException) {

            $exception_code = $exception->getCode();
            $response['response_code'] = (!empty($exception_code)) ? $exception_code : 102;
            $response['message'] = $exception->getMessage();
            return response()->json($response, 500);
        }

        return parent::render($request, $exception);
    }

}
