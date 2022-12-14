<?php

namespace App\Services\LaravelWebSockets\src\Tests\Messages;

use App\Services\LaravelWebSockets\src\Tests\TestCase;
use App\Services\LaravelWebSockets\src\Tests\Mocks\Message;

class PusherClientMessageTest extends TestCase
{
    /** @test */
    public function client_messages_do_not_work_when_disabled()
    {
        $connection = $this->getConnectedWebSocketConnection(['test-channel']);

        $message = new Message(json_encode([
            'event' => 'client-test',
            'channel' => 'test-channel',
            'data' => [
                'client-event' => 'test',
            ],
        ]));

        $this->pusherServer->onMessage($connection, $message);

        $connection->assertNotSentEvent('client-test');
    }

    /** @test */
    public function client_messages_get_broadcasted_when_enabled()
    {
        $this->app['config']->set('websockets.apps', [
            [
                'name' => 'Test App',
                'id' => 1234,
                'key' => 'TestKey',
                'secret' => 'TestSecret',
                'enable_client_messages' => true,
                'enable_statistics' => true,
            ],
        ]);

        $connection1 = $this->getConnectedWebSocketConnection(['test-channel']);
        $connection2 = $this->getConnectedWebSocketConnection(['test-channel']);

        $message = new Message(json_encode([
            'event' => 'client-test',
            'channel' => 'test-channel',
            'data' => [
                'client-event' => 'test',
            ],
        ]));

        $this->pusherServer->onMessage($connection1, $message);

        $connection1->assertNotSentEvent('client-test');

        $connection2->assertSentEvent('client-test', [
            'data' => [
                'client-event' => 'test',
            ],
        ]);
    }
}
