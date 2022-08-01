<?php

namespace App\Services\LaravelWebSockets\src\Tests\Mocks;

class Message extends \Ratchet\RFC6455\Messaging\Message
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function getPayload()
    {
        return $this->payload;
    }
}
