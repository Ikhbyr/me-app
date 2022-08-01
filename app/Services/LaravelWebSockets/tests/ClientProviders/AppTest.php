<?php

namespace App\Services\LaravelWebSockets\src\Tests\ClientProviders;

use App\Services\LaravelWebSockets\src\Apps\App;
use App\Services\LaravelWebSockets\src\Tests\TestCase;
use App\Services\LaravelWebSockets\src\Exceptions\InvalidApp;

class AppTest extends TestCase
{
    /** @test */
    public function it_can_create_a_client()
    {
        new App(1, 'appKey', 'appSecret', 'new');

        $this->markTestAsPassed();
    }

    /** @test */
    public function it_will_not_accept_an_empty_appKey()
    {
        $this->expectException(InvalidApp::class);

        new App(1, '', 'appSecret', 'new');
    }

    /** @test */
    public function it_will_not_accept_an_empty_appSecret()
    {
        $this->expectException(InvalidApp::class);

        new App(1, 'appKey', '', 'new');
    }
}
