<?php

namespace App\Services\LaravelWebSockets\src\Statistics\Http\Middleware;

use App\Services\LaravelWebSockets\src\Apps\App;

class Authorize
{
    public function handle($request, $next)
    {
        return is_null(App::findBySecret($request->secret)) ? abort(403) : $next($request);
    }
}
