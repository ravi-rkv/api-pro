<?php

namespace App\Exceptions;

use Throwable;
use Psr\Log\LogLevel;
use Illuminate\Support\Str;
use App\Services\ApiResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Generate a unique request ID
        $requestId = (string) Str::uuid();
        $request->merge(['request_id' => $requestId]);

        // Handle specific exceptions
        if ($exception instanceof MethodNotAllowedHttpException) {
            return ApiResponse::response(
                'IPE',
                'HTTP Method Not Allowed , The requested HTTP method is not allowed for this route.',
                [],
                405
            );
        }

        if ($exception instanceof NotFoundHttpException) {
            return ApiResponse::response(
                'IPE',
                'Route Not Found , The requested route could not be found.',
                [],
                404
            );
        }

        // Call parent render for other exceptions
        return parent::render($request, $exception);
    }
}
