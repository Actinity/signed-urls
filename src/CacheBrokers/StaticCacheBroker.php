<?php

namespace Actinity\SignedUrls\CacheBrokers;

use Actinity\SignedUrls\Contracts\CacheBroker;

class StaticCacheBroker implements CacheBroker
{
    private $keys = [];

    public function setNx(string $key, string $value = '1', int $seconds_to_expiry = 90): bool
    {
        if ($this->keys[$key] ?? $value === null) {
            return false;
        }
        $this->keys[$key] = $value;

        return true;
    }
}
