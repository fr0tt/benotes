<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
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
            return response()->json('Token has expired', Response::HTTP_UNAUTHORIZED);
        } else if (
            $exception instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException ||
            $exception instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException
        ) {
            return response()->json('Token is invalid', Response::HTTP_UNAUTHORIZED);
        } else if ($exception instanceof \Intervention\Image\Exception\NotWritableException) {
            return response()->json('Storage path not writable.', Response::HTTP_INTERNAL_SERVER_ERROR);
        } else if ($exception instanceof AuthorizationException) {
            return response()->json('This action is unauthorized.', Response::HTTP_UNAUTHORIZED);
        } else if ($exception instanceof ModelNotFoundException) {
            return response()->json(
                str_replace('App\\', '', $exception->getModel()) . ' not found.',
                Response::HTTP_NOT_FOUND
            );
        } else if ($exception instanceof \UnexpectedValueException) {
            if (!is_writable(storage_path()) || !is_writable(storage_path('logs'))) {
                return response()->json('Storage path is not writable.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return parent::render($request, $exception);
    }
}
