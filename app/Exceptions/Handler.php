<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

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
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof UnauthorizedException) {
            if ($exception->getMessage() == "token_expire") {
                return response([
                    'code' => 'token_expire',
                    'message' => 'ログインされていません。もう一度ログインしてください。'
                ], 401);
            } else if ($exception->getMessage() == "jwt-auth") {
                return response([
                    'code' => 'token_not_found',
                    'message' => 'Token không tồn tại!'
                ], 401);
            } else {
                return response([
                    'code' => 'unauthorized',
                    'message' => 'ログインされていません。もう一度ログインしてください。!'
                ], 401);
            }
        }
        return parent::render($request, $exception);
    }
}
