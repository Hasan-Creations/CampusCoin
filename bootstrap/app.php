<?php

use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// Ensure this worktree's App, Database, and Tests namespaces are strictly resolved from this worktree
spl_autoload_register(function (string $class): bool {
    $base = dirname(__DIR__);
    if (str_starts_with($class, 'App\\')) {
        $path = $base.'/app/'.str_replace('\\', '/', substr($class, 4)).'.php';
        if (file_exists($path)) {
            require_once $path;

            return true;
        }
    }
    if (str_starts_with($class, 'Database\\')) {
        $path = $base.'/database/'.str_replace('\\', '/', substr($class, 9)).'.php';
        if (file_exists($path)) {
            require_once $path;

            return true;
        }
    }
    if (str_starts_with($class, 'Tests\\')) {
        $path = $base.'/tests/'.str_replace('\\', '/', substr($class, 6)).'.php';
        if (file_exists($path)) {
            require_once $path;

            return true;
        }
    }

    return false;
}, prepend: true);

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
