<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        api:      __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Middleware aliases
        $middleware->alias([
            'role'  => \App\Http\Middleware\CheckRole::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'check.chat.permission' => \App\Http\Middleware\CheckChatPermission::class,
            'block.inappropriate'   => \App\Http\Middleware\BlockInappropriateContent::class,
            'ensure.teacher.record' => \App\Http\Middleware\EnsureTeacherRecordExists::class,
        ]);

        // Global locale middleware for web
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Show detailed Laravel error messages
        // Custom error pages disabled for debugging - error details now visible
    })->create();
