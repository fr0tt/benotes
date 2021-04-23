<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        //ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
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
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json('Token has expired', 401);
        } else if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException ||
                   $exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
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
