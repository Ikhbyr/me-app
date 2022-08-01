<?php

namespace App\AuditResolvers;

use Exception;
use Illuminate\Support\Facades\Request;

class IpAddressResolver implements \OwenIt\Auditing\Contracts\IpAddressResolver
{
    /**
     * {@inheritdoc}
     */
    public static function resolve(): string
    {
        if (array_key_exists('HTTP_X_REAL_IP', $_SERVER)) return $_SERVER['HTTP_X_REAL_IP'];
        return Request::ip();
    }
}
