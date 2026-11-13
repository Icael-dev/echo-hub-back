<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // For API routes, return 401 JSON instead of redirecting to login
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('api/*')) {
                return null; // Don't redirect for API
            }
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        // Return clean 401 JSON for unauthenticated API requests instead of redirecting to login route
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') && $e instanceof AuthenticationException) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });
    })->create();
