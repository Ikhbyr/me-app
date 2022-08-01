<?php

namespace App\Services\LaravelWebSockets\src\Apps;

interface AppProvider
{
    /**  @return array[App\Services\LaravelWebSockets\src\AppProviders\App] */
    public function all(): array;

    public function findById($appId): ?App;

    public function findByKey(string $appKey): ?App;

    public function findBySecret(string $appSecret): ?App;
}
