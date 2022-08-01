<?php

namespace App\Services\LaravelWebSockets\src\Statistics\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Services\LaravelWebSockets\src\Dashboard\DashboardLogger;
use App\Services\LaravelWebSockets\src\Statistics\Models\WebSocketsStatisticsEntry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StatisticsUpdated implements ShouldBroadcast
{
    use SerializesModels;

    /** @var \App\Services\LaravelWebSockets\src\Statistics\Models\WebSocketsStatisticsEntry */
    protected $webSocketsStatisticsEntry;

    public function __construct(WebSocketsStatisticsEntry $webSocketsStatisticsEntry)
    {
        $this->webSocketsStatisticsEntry = $webSocketsStatisticsEntry;
    }

    public function broadcastWith()
    {
        return [
            'time' => (string) $this->webSocketsStatisticsEntry->created_at,
            'app_id' => $this->webSocketsStatisticsEntry->app_id,
            'peak_connection_count' => $this->webSocketsStatisticsEntry->peak_connection_count,
            'websocket_message_count' => $this->webSocketsStatisticsEntry->websocket_message_count,
            'api_message_count' => $this->webSocketsStatisticsEntry->api_message_count,
        ];
    }

    public function broadcastOn()
    {
        $channelName = Str::after(DashboardLogger::LOG_CHANNEL_PREFIX.'statistics', 'private-');
        // Log::info("channel ".$channelName);
        return new PrivateChannel($channelName);
    }

    public function broadcastAs()
    {
        return 'statistics-updated';
    }
}
