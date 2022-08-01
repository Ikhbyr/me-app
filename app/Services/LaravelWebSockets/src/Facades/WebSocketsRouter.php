<?php

namespace App\Services\LaravelWebSockets\src\Facades;

use Illuminate\Support\Facades\Facade;

/** @see \App\Services\LaravelWebSockets\src\Server\Router */
class WebSocketsRouter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'websockets.router';
    }
}
