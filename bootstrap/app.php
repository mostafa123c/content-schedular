<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, Request $request) {
            return match (true) {
                $e instanceof RouteNotFoundException    => response()->json(['success' => false, 'message' => 'model not found'], 404),
                $e instanceof NotFoundHttpException     => response()->json(['success' => false, 'message' => 'route not found'], 404),
                $e instanceof AuthenticationException   => response()->json(['success' => false, 'message' => $e->getMessage()], 401),
                $e instanceof ValidationException       => response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422),
                $e instanceof UnauthorizedException     => response()->json(['success' => false, 'message' => $e->getMessage()], 403),
                $e instanceof AuthorizationException    => response()->json(['success' => false, 'message' => $e->getMessage()], 403),
                $e instanceof AccessDeniedHttpException => response()->json(['success' => false, 'message' => $e->getMessage()], 403),
                default                                 => response()->json(['success' => false, 'message' => $e->getMessage()], 500),
            };
        });
    })->create();
