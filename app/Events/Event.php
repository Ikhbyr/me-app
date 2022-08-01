<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

abstract class Event
{
    abstract public function eventName();

    abstract public function data();

    public function __toString()
    {
        return json_encode([
            'event' => $this->eventName(),
            'data' => $this->data(),
        ]);
    }
}
