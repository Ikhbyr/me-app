<?php

namespace App\Services\LaravelWebSockets\src\Console;

use React\Socket\Connector;
use React\Http\Browser;
use Illuminate\Console\Command;
use React\Dns\Config\Config as DnsConfig;
use React\EventLoop\Factory as LoopFactory;
use React\Dns\Resolver\Factory as DnsFactory;
use React\Dns\Resolver\Resolver as ReactDnsResolver;
use App\Services\LaravelWebSockets\src\Statistics\DnsResolver;
use App\Services\LaravelWebSockets\src\Server\Logger\HttpLogger;
use App\Services\LaravelWebSockets\src\Server\WebSocketServerFactory;
use App\Services\LaravelWebSockets\src\Server\Logger\ConnectionLogger;
use App\Services\LaravelWebSockets\src\Server\Logger\WebsocketsLogger;
use App\Services\LaravelWebSockets\src\WebSockets\Channels\ChannelManager;
use App\Services\LaravelWebSockets\src\Statistics\Logger\HttpStatisticsLogger;
use App\Services\LaravelWebSockets\src\Statistics\Logger\StatisticsLogger;

class StartWebSocketServer extends Command
{
    protected $signature = 'websockets:serve {--host=0.0.0.0} {--port=6001} {--debug : Forces the loggers to be enabled and thereby overriding the app.debug config setting } ';

    protected $description = 'Start the Laravel WebSocket Server';

    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    public function __construct()
    {
        parent::__construct();

        $this->loop = LoopFactory::create();
    }

    public function handle()
    {
        $this
            ->configureStatisticsLogger()
            ->configureHttpLogger()
            ->configureMessageLogger()
            ->configureConnectionLogger()
            ->registerEchoRoutes()
            ->startWebSocketServer();
    }

    protected function configureStatisticsLogger()
    {
        $connector = new Connector($this->loop, [
            'dns' => $this->getDnsResolver(),
            'tls' => [
                'verify_peer' => config('app.env') === 'production',
                'verify_peer_name' => config('app.env') === 'production',
            ],
        ]);

        $browser = new Browser($this->loop, $connector);

        app()->singleton(StatisticsLogger::class, function () use ($browser) {
            return new HttpStatisticsLogger(app(ChannelManager::class), $browser);
        });

        $this->loop->addPeriodicTimer(config('websockets.statistics.interval_in_seconds'), function () {
            app(StatisticsLogger::class)->save();
        });

        return $this;
    }

    protected function configureHttpLogger()
    {
        app()->singleton(HttpLogger::class, function () {
            return (new HttpLogger($this->output))
                ->enable($this->option('debug') ?: config('app.debug'))
                ->verbose($this->output->isVerbose());
        });

        return $this;
    }

    protected function configureMessageLogger()
    {
        app()->singleton(WebsocketsLogger::class, function () {
            return (new WebsocketsLogger($this->output))
                ->enable($this->option('debug') ?: config('app.debug'))
                ->verbose($this->output->isVerbose());
        });

        return $this;
    }

    protected function configureConnectionLogger()
    {
        app()->bind(ConnectionLogger::class, function () {
            return (new ConnectionLogger($this->output))
                ->enable(config('app.debug'))
                ->verbose($this->output->isVerbose());
        });

        return $this;
    }

    protected function registerEchoRoutes()
    {
        app('websockets.router')->echo();

        return $this;
    }

    protected function startWebSocketServer()
    {
        $this->info("Starting the WebSocket server on port {$this->option('port')}...");

        $routes = app('websockets.router')->getRoutes();

        /* ???? Start the server ????  */
        (new WebSocketServerFactory())
            ->setLoop($this->loop)
            ->useRoutes($routes)
            ->setHost($this->option('host'))
            ->setPort($this->option('port'))
            ->setConsoleOutput($this->output)
            ->createServer()
            ->run();
    }

    protected function getDnsResolver(): ReactDnsResolver
    {
        if (! config('websockets.statistics.perform_dns_lookup')) {
            return new DnsResolver;
        }

        $dnsConfig = DnsConfig::loadSystemConfigBlocking();

        return (new DnsFactory)->createCached(
            $dnsConfig->nameservers
                ? reset($dnsConfig->nameservers)
                : '1.1.1.1',
            $this->loop
        );
    }
}
