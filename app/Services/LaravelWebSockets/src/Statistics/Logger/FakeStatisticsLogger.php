<?php

namespace App\Services\LaravelWebSockets\src\Statistics\Logger;

use Ratchet\ConnectionInterface;

class FakeStatisticsLogger implements StatisticsLogger
{
    public function webSocketMessage(ConnectionInterface $connection)
    {
    }

    public function apiMessage($appId)
    {
    }

    public function connection(ConnectionInterface $connection)
    {
    }

    public function disconnection(ConnectionInterface $connection)
    {
    }

    protected function initializeStatistics($id)
    {
    }

    public function save()
    {
    }
}
