<?php

namespace App\Services\LaravelWebSockets\src\WebSockets\Messages;

use stdClass;
use Ratchet\ConnectionInterface;
use App\Services\LaravelWebSockets\src\Dashboard\DashboardLogger;
use App\Services\LaravelWebSockets\src\WebSockets\Channels\ChannelManager;

class PusherClientMessage implements PusherMessage
{
    /** \stdClass */
    protected $payload;

    /** @var \Ratchet\ConnectionInterface */
    protected $connection;

    /** @var \App\Services\LaravelWebSockets\src\WebSockets\Channels\ChannelManager */
    protected $channelManager;

    public function __construct(stdClass $payload, ConnectionInterface $connection, ChannelManager $channelManager)
    {
        $this->payload = $payload;

        $this->connection = $connection;

        $this->channelManager = $channelManager;
    }

    public function respond()
    {
        if (! starts_with($this->payload->event, 'client-')) {
            return;
        }

        if (! $this->connection->app->clientMessagesEnabled) {
            return;
        }

        DashboardLogger::clientMessage($this->connection, $this->payload);

        $channel = $this->channelManager->find($this->connection->app->id, $this->payload->channel);

        optional($channel)->broadcastToOthers($this->connection, $this->payload);
    }
}
