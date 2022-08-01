<?php

namespace App\Services\LaravelWebSockets\src\Dashboard\Http\Controllers;

use Pusher\Pusher;
use Illuminate\Http\Request;
use App\Services\LaravelWebSockets\src\Statistics\Rules\AppId;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SendMessage extends Controller
{
    public function send(Request $request)
    {
        $validated = $this->validate($request, [
            'appId' => ['required', new AppId()],
            'key' => 'required',
            'secret' => 'required',
            'channel' => 'required',
            'event' => 'required',
            'data' => 'json',
        ]);

        $this->getPusherBroadcaster($validated)->broadcast(
            [$validated['channel']],
            $validated['event'],
            json_decode($validated['data'], true)
        );

        return 'ok';
    }

    protected function getPusherBroadcaster(array $validated): PusherBroadcaster
    {
        $options = [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            // 'encrypted' => true,
            'host' => '127.0.0.1',
            'port' => 6001,
            'scheme' => 'http'
        ];
        // Log::info($options);
        $pusher = new Pusher(
            $validated['key'],
            $validated['secret'],
            $validated['appId'],
            $options
        );

        return new PusherBroadcaster($pusher);
    }
}
