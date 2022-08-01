<?php

namespace App\Services\LaravelWebSockets\src\Statistics\Logger;

use React\Http\Browser;
use Ratchet\ConnectionInterface;
use function GuzzleHttp\Psr7\stream_for;
use App\Services\LaravelWebSockets\src\Apps\App;
use App\Services\LaravelWebSockets\src\Statistics\Statistic;
use App\Services\LaravelWebSockets\src\WebSockets\Channels\ChannelManager;
use App\Services\LaravelWebSockets\src\Statistics\Http\Controllers\WebSocketStatisticsEntriesController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpStatisticsLogger implements StatisticsLogger
{
    /** @var \App\Services\LaravelWebSockets\src\Statistics\Statistic[] */
    protected $statistics = [];

    /** @var \App\Services\LaravelWebSockets\src\WebSockets\Channels\ChannelManager */
    protected $channelManager;

    /** @var \Clue\React\Buzz\Browser */
    protected $browser;

    public function __construct(ChannelManager $channelManager, Browser $browser)
    {
        $this->channelManager = $channelManager;

        $this->browser = $browser;
    }

    public function webSocketMessage(ConnectionInterface $connection)
    {
        $this
            ->findOrMakeStatisticForAppId($connection->app->id)
            ->webSocketMessage();
    }

    public function apiMessage($appId)
    {
        $this
            ->findOrMakeStatisticForAppId($appId)
            ->apiMessage();
    }

    public function connection(ConnectionInterface $connection)
    {
        $this
            ->findOrMakeStatisticForAppId($connection->app->id)
            ->connection();
    }

    public function disconnection(ConnectionInterface $connection)
    {
        $this
            ->findOrMakeStatisticForAppId($connection->app->id)
            ->disconnection();
    }

    protected function findOrMakeStatisticForAppId($appId): Statistic
    {
        if (! isset($this->statistics[$appId])) {
            $this->statistics[$appId] = new Statistic($appId);
        }

        return $this->statistics[$appId];
    }

    public function save()
    {
        foreach ($this->statistics as $appId => $statistic) {
            if (! $statistic->isEnabled()) {
                continue;
            }

            $postData = array_merge($statistic->toArray(), [
                'secret' => App::findById($appId)->secret,
            ]);
            // Http::post(app('url')->route('statistics'), $postData);
            $currentConnectionCount = $this->channelManager->getConnectionCount($appId);
            $statistic->reset($currentConnectionCount);
        }
    }
}
