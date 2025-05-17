<?php

namespace Actinity\SignedUrls\Contracts;

interface CacheBroker
{
    public function setNx(string $key, string $value = '1', int $seconds_to_expiry = 90): bool;
}
