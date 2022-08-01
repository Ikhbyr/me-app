<?php

namespace App\Services\LaravelWebSockets\src\Dashboard\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LaravelWebSockets\src\Apps\AppProvider;

class ShowDashboard
{
    public function __invoke(Request $request, AppProvider $apps)
    {
        return view('websockets::dashboard', [
            'apps' => $apps->all(),
        ]);
    }
}
