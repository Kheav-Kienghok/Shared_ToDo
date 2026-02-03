<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // Rate limit exceeded
        if ($e instanceof ThrottleRequestsException) {
            return response()->json([
                'status' => 'error',
                'error' => 'Too many attempts. Try again later.',
            ], 429);
        }

        // Route not found
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'status' => 'error',
                'error' => 'Endpoint not found.',
            ], 404);
        }

        // Model not found
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'error' => 'Resource not found.',
            ], 404);
        }

        // Method not allowed
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'status' => 'error',
                'error' => 'Method not allowed for this endpoint.',
            ], 405);
        }

        return parent::render($request, $e);
    }
}
