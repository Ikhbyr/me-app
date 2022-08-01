<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewMessageEvent extends EventTest implements ShouldBroadcast
{
    public $message;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($channel, $message)
    {
        //
        $this->message = $message;
    }
    public function broadcastOn()
    {
        Log::debug("matar");
        return (string)'my-channel';
        // return new PrivateChannel('my-channel');
    }

    public function broadcastAs()
    {
        return 'my-event';
    }
}
