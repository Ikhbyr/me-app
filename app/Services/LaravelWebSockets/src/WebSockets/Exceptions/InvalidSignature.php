<?php

namespace App\Services\LaravelWebSockets\src\WebSockets\Exceptions;

class InvalidSignature extends WebSocketException
{
    public function __construct()
    {
        $this->message = 'Invalid Signature';

        $this->code = 4009;
    }
}
