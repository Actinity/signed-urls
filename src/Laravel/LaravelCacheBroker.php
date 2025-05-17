<?php

namespace Actinity\SignedUrls\Laravel;

use Actinity\SignedUrls\Contracts\CacheBroker;

class LaravelCacheBroker implements CacheBroker
{
    public function setNx(string $key, string $value = '1', int $seconds_to_expiry = 90): bool
    {
        return cache()->add($key, $value, $seconds_to_expiry);
    }
}
