<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
     * @throws \Throwable
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

        // if ($exception instanceof ValidationException) {
           
        //     // Check if the request is expecting JSON
        //     if ($request->expectsJson()) {
        //         return new JsonResponse([
        //             'message' => 'The given data was invalid.',
        //             'errors' => $exception->errors(),
        //         ], 422); // Use the appropriate HTTP status code (422 Unprocessable Entity)
        //     }
        // }

        if ($exception instanceof HttpException && $exception->getStatusCode() === 403) {
           
            return response()->json(['message' => $exception->getMessage() ?: 'Forbidden'], 403);
        }

        return parent::render($request, $exception);
    }
}
