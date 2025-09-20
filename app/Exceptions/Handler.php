<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Handle HttpExceptions (404, 405, etc.)
        if ($exception instanceof HttpExceptionInterface) {
            switch ($exception->getStatusCode()) {
                case 404:
                case 405:
                case 500:
                    return redirect('/', 301);
            }
        }

        // Handle generic non-Http exceptions (often cause 500)
        if (! $exception instanceof HttpExceptionInterface) {
            return redirect('/', 301);
        }

        return parent::render($request, $exception);
    }
}
