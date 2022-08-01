<?php

namespace App\Services\LaravelWebSockets\src\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\LaravelWebSockets\src\Statistics\Logger\FakeStatisticsLogger;
use App\Services\LaravelWebSockets\src\Statistics\Logger\StatisticsLogger as StatisticsLoggerInterface;

/** @see \App\Services\LaravelWebSockets\src\Statistics\Logger\HttpStatisticsLogger */
class StatisticsLogger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return StatisticsLoggerInterface::class;
    }

    public static function fake()
    {
        static::swap(new FakeStatisticsLogger());
    }
}
