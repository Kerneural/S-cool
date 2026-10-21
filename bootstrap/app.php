<?php

use App\Http\Middleware\EnsureUserIsPlatformAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Invitation/auth secrets passed to actions must not enter exception stack traces.
ini_set('zend.exception_ignore_args', '1');

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Preserve URL controls for validation instead of silently trimming unsafe input.
        $middleware->trimStrings(except: ['github_url', 'portfolio_url']);

        $middleware->validateCsrfTokens(except: [
            'webhooks/sepay',
        ]);

        $middleware->alias([
            'platform-admin' => EnsureUserIsPlatformAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash(['token']);
    })->create();
