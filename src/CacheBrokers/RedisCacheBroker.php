<?php

namespace Actinity\SignedUrls\CacheBrokers;

use Actinity\SignedUrls\Contracts\CacheBroker;

class RedisCacheBroker implements CacheBroker
{
    protected $redis;

    public function setNx(string $key, string $value = '1', int $seconds_to_expiry = 90): bool
    {
        return (bool) $this->redis->set($key, $value, ['nx', 'ex' => $seconds_to_expiry]);
    }
}
