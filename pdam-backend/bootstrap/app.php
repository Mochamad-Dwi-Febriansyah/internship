<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckToken;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsKepegawaian;
use App\Http\Middleware\IsMentor;
use App\Http\Middleware\IsUser;
use Illuminate\Foundation\Application;
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
        $middleware->alias([
            'cekToken' => CheckToken::class,
            'isAdmin' => IsAdmin::class,
            'isUser' => IsUser::class,
            'isMentor' => IsMentor::class,
            'isKepegawaian' => IsKepegawaian::class,
            'checkRole' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
