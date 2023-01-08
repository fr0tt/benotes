<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        //ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json('Token has expired', 401);
        } else if ($exception instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException ||
                   $exception instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException) {
            return response()->json('Token is invalid', 401);
        } else if ($exception instanceof \Intervention\Image\Exception\NotWritableException) {
            return response()->json('Storage path not writable.', 403);
        } else if ($exception instanceof AuthorizationException) {
            return response()->json('This action is unauthorized.', 403);
        } else if ($exception instanceof ModelNotFoundException) {
            return response()->json(
                str_replace('App\\', '', $exception->getModel()) . ' not found.'
            , 404);
        }

        return parent::render($request, $exception);
    }

}
