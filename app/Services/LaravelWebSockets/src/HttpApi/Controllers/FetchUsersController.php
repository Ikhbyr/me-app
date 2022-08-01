<?php

namespace App\Services\LaravelWebSockets\src\HttpApi\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Services\LaravelWebSockets\src\WebSockets\Channels\PresenceChannel;

class FetchUsersController extends Controller
{
    public function __invoke(Request $request)
    {
        $channel = $this->channelManager->find($request->appId, $request->channelName);

        if (is_null($channel)) {
            throw new HttpException(404, 'Unknown channel "'.$request->channelName.'"');
        }

        if (! $channel instanceof PresenceChannel) {
            throw new HttpException(400, 'Invalid presence channel "'.$request->channelName.'"');
        }

        return [
            'users' => Collection::make($channel->getUsers())->map(function ($user) {
                return ['id' => $user->user_id];
            })->values(),
        ];
    }
}
