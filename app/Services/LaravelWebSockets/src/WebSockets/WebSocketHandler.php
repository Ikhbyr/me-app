<?php

namespace App\Services\LaravelWebSockets\src\WebSockets;

use Exception;
use Ratchet\ConnectionInterface;
use App\Services\LaravelWebSockets\src\Apps\App;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use App\Services\LaravelWebSockets\src\QueryParameters;
use App\Services\LaravelWebSockets\src\Facades\StatisticsLogger;
use App\Services\LaravelWebSockets\src\Dashboard\DashboardLogger;
use App\Services\LaravelWebSockets\src\WebSockets\Channels\ChannelManager;
use App\Services\LaravelWebSockets\src\WebSockets\Exceptions\UnknownAppKey;
use App\Services\LaravelWebSockets\src\WebSockets\Exceptions\WebSocketException;
use App\Services\LaravelWebSockets\src\WebSockets\Messages\PusherMessageFactory;

class WebSocketHandler implements MessageComponentInterface
{
    /** @var \App\Services\LaravelWebSockets\src\WebSockets\Channels\ChannelManager */
    protected $channelManager;

    public function __construct(ChannelManager $channelManager)
    {
        $this->channelManager = $channelManager;
    }

    public function onOpen(ConnectionInterface $connection)
    {
        $this
            ->verifyAppKey($connection)
            ->generateSocketId($connection)
            ->establishConnection($connection);
    }

    public function onMessage(ConnectionInterface $connection, MessageInterface $message)
    {
        $message = PusherMessageFactory::createForMessage($message, $connection, $this->channelManager);

        $message->respond();

        StatisticsLogger::webSocketMessage($connection);
    }

    public function onClose(ConnectionInterface $connection)
    {
        $this->channelManager->removeFromAllChannels($connection);

        DashboardLogger::disconnection($connection);

        StatisticsLogger::disconnection($connection);
    }

    public function onError(ConnectionInterface $connection, Exception $exception)
    {
        if ($exception instanceof WebSocketException) {
            $connection->send(json_encode(
                $exception->getPayload()
            ));
        }
    }

    protected function verifyAppKey(ConnectionInterface $connection)
    {
        $appKey = QueryParameters::create($connection->httpRequest)->get('appKey');

        if (! $app = App::findByKey($appKey)) {
            throw new UnknownAppKey($appKey);
        }

        $connection->app = $app;

        return $this;
    }

    protected function generateSocketId(ConnectionInterface $connection)
    {
        $socketId = sprintf('%d.%d', random_int(1, 1000000000), random_int(1, 1000000000));

        $connection->socketId = $socketId;

        return $this;
    }

    protected function establishConnection(ConnectionInterface $connection)
    {
        $connection->send(json_encode([
            'event' => 'pusher:connection_established',
            'data' => json_encode([
                'socket_id' => $connection->socketId,
                'activity_timeout' => 30,
            ]),
        ]));

        DashboardLogger::connection($connection);

        StatisticsLogger::connection($connection);

        return $this;
    }
}
