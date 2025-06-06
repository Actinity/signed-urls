<?php

namespace Actinity\SignedUrls\CacheBrokers;

use Predis\Client;

class PredisCacheBroker extends RedisCacheBroker
{
    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }
}
