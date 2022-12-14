<?php

namespace App\Services\LaravelWebSockets\src\Tests;

use GuzzleHttp\Psr7\Request;
use Ratchet\ConnectionInterface;
use App\Services\LaravelWebSockets\src\Tests\Mocks\Message;
use App\Services\LaravelWebSockets\src\Tests\Mocks\Connection;
use App\Services\LaravelWebSockets\src\Facades\StatisticsLogger;
use App\Services\LaravelWebSockets\src\WebSocketsServiceProvider;
use App\Services\LaravelWebSockets\src\WebSockets\WebSocketHandler;
use App\Services\LaravelWebSockets\src\WebSockets\Channels\ChannelManager;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /** @var \App\Services\LaravelWebSockets\src\WebSockets\WebSocketHandler */
    protected $pusherServer;

    /** @var \App\Services\LaravelWebSockets\src\WebSockets\Channels\ChannelManager */
    protected $channelManager;

    public function setUp()
    {
        parent::setUp();

        $this->pusherServer = app(WebSocketHandler::class);

        $this->channelManager = app(ChannelManager::class);

        StatisticsLogger::fake();
    }

    protected function getPackageProviders($app)
    {
        return [WebSocketsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('websockets.apps', [
            [
                'name' => 'Test App',
                'id' => 1234,
                'key' => 'TestKey',
                'secret' => 'TestSecret',
                'enable_client_messages' => false,
                'enable_statistics' => true,
            ],
        ]);

        include_once __DIR__.'/../database/migrations/create_websockets_statistics_entries_table.php.stub';

        (new \CreateWebSocketsStatisticsEntriesTable())->up();
    }

    protected function getWebSocketConnection(string $url = '/?appKey=TestKey'): Connection
    {
        $connection = new Connection();

        $connection->httpRequest = new Request('GET', $url);

        return $connection;
    }

    protected function getConnectedWebSocketConnection(array $channelsToJoin = [], string $url = '/?appKey=TestKey'): Connection
    {
        $connection = new Connection();

        $connection->httpRequest = new Request('GET', $url);

        $this->pusherServer->onOpen($connection);

        foreach ($channelsToJoin as $channel) {
            $message = new Message(json_encode([
                'event' => 'pusher:subscribe',
                'data' => [
                    'channel' => $channel,
                ],
            ]));

            $this->pusherServer->onMessage($connection, $message);
        }

        return $connection;
    }

    protected function joinPresenceChannel($channel): Connection
    {
        $connection = $this->getWebSocketConnection();

        $this->pusherServer->onOpen($connection);

        $channelData = [
            'user_id' => 1,
            'user_info' => [
                'name' => 'Marcel',
            ],
        ];

        $signature = "{$connection->socketId}:{$channel}:".json_encode($channelData);

        $message = new Message(json_encode([
            'event' => 'pusher:subscribe',
            'data' => [
                'auth' => $connection->app->key.':'.hash_hmac('sha256', $signature, $connection->app->secret),
                'channel' => $channel,
                'channel_data' => json_encode($channelData),
            ],
        ]));

        $this->pusherServer->onMessage($connection, $message);

        return $connection;
    }

    protected function getChannel(ConnectionInterface $connection, string $channelName)
    {
        return $this->channelManager->findOrCreate($connection->app->id, $channelName);
    }

    protected function markTestAsPassed()
    {
        $this->assertTrue(true);
    }
}
