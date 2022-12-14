<?php

namespace App\Services\LaravelWebSockets\src;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate;
use App\Services\LaravelWebSockets\src\Server\Router;
use App\Services\LaravelWebSockets\src\Apps\AppProvider;
use App\Services\LaravelWebSockets\src\WebSockets\Channels\ChannelManager;
use App\Services\LaravelWebSockets\src\Dashboard\Http\Controllers\SendMessage;
use App\Services\LaravelWebSockets\src\Dashboard\Http\Controllers\ShowDashboard;
use App\Services\LaravelWebSockets\src\Dashboard\Http\Controllers\AuthenticateDashboard;
use App\Services\LaravelWebSockets\src\Dashboard\Http\Controllers\DashboardApiController;
use App\Services\LaravelWebSockets\src\WebSockets\Channels\ChannelManagers\ArrayChannelManager;
use App\Services\LaravelWebSockets\src\Dashboard\Http\Middleware\Authorize as AuthorizeDashboard;
use App\Services\LaravelWebSockets\src\Statistics\Http\Middleware\Authorize as AuthorizeStatistics;
use App\Services\LaravelWebSockets\src\Statistics\Http\Controllers\WebSocketStatisticsEntriesController;

class WebSocketsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/websockets.php' => base_path('config/websockets.php'),
        ], 'config');

        if (!class_exists('CreateWebSocketsStatisticsEntries')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_websockets_statistics_entries_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_websockets_statistics_entries_table.php'),
            ], 'migrations');
        }

        $this
            ->registerRoutes()
            ->registerDashboardGate();

        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'websockets');

        $this->commands([
            Console\StartWebSocketServer::class,
            Console\CleanStatistics::class,
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/websockets.php', 'websockets');

        $this->app->singleton('websockets.router', function () {
            return new Router();
        });

        $this->app->singleton(ChannelManager::class, function () {
            return config('websockets.channel_manager') !== null && class_exists(config('websockets.channel_manager'))
                ? app(config('websockets.channel_manager')) : new ArrayChannelManager();
        });

        $this->app->singleton(AppProvider::class, function () {
            return app(config('websockets.app_provider'));
        });
    }

    protected function registerRoutes()
    {
        app('router')->group([
            'prefix' => config('websockets.path'),
            'middleware' => AuthorizeDashboard::class,
        ], function () {
            app('router')->get('/', ShowDashboard::class);
            app('router')->get('/api/{appId}/statistics', 'App\Services\LaravelWebSockets\src\Dashboard\Http\Controllers\DashboardApiController@getStatistics');
            app('router')->post('auth', AuthenticateDashboard::class);
            app('router')->post('event', 'App\Services\LaravelWebSockets\src\Dashboard\Http\Controllers\SendMessage@send');
            app('router')->group(['middleware' => AuthorizeStatistics::class], function () {
                app('router')->post('statistics', [
                    'as' => 'statistics',
                    'uses' => 'App\Services\LaravelWebSockets\src\Statistics\Http\Controllers\WebSocketStatisticsEntriesController@store'
                ]);
            });
        });



        return $this;
    }

    protected function registerDashboardGate()
    {
        app(Gate::class)->define('viewWebSocketsDashboard', function ($user = null) {
            return app()->environment('local');
        });

        return $this;
    }
}
