<?php

namespace App\Services\LaravelWebSockets\src\WebSockets\Exceptions;

use Exception;

class WebSocketException extends Exception
{
    public function getPayload()
    {
        return [
            'event' => 'pusher:error',
            'data' => [
                'message' => $this->getMessage(),
                'code' => $this->getCode(),
            ],
        ];
    }
}
