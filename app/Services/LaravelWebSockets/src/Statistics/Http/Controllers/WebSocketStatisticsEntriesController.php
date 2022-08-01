<?php

namespace App\Services\LaravelWebSockets\src\Statistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LaravelWebSockets\src\Statistics\Rules\AppId;
use App\Services\LaravelWebSockets\src\Statistics\Events\StatisticsUpdated;
use Illuminate\Support\Facades\Log;

class WebSocketStatisticsEntriesController extends Controller
{
    public function store(Request $request)
    {
        $validatedAttributes = $this->validate($request, [
            'app_id' => ['required', new AppId()],
            'peak_connection_count' => 'required|integer',
            'websocket_message_count' => 'required|integer',
            'api_message_count' => 'required|integer',
        ]);

        // Log::info($validatedAttributes);
        $webSocketsStatisticsEntryModelClass = config('websockets.statistics.model');

        $statisticModel = $webSocketsStatisticsEntryModelClass::create($validatedAttributes);

        broadcast(new StatisticsUpdated($statisticModel));

        return 'ok';
    }
}
