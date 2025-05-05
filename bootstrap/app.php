<?php

use App\Http\Middleware\AuthToken;
use Illuminate\Foundation\Application;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UpdateBookingStatus;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(UpdateBookingStatus::class);
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'authToken' => AuthToken::class,
            // 'participant' => ParticipantMiddleware::class,
            // 'mentor' => MentorMiddleware::class,
            // 'authToken' => AuthToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
