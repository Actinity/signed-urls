<?php

namespace Actinity\SignedUrlTests;

use Actinity\SignedUrls\CacheBrokers\StaticCacheBroker;
use Actinity\SignedUrls\GeneratedKeyPair;
use Actinity\SignedUrls\KeyProviders\ConfigKeyProvider;
use Actinity\SignedUrls\SignedUrlService;

abstract class SigningCase extends TestCase
{
    protected $privateKey;

    protected $publicKey;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $pair = new GeneratedKeyPair;
        $this->privateKey = $pair->getPrivate();
        $this->publicKey = $pair->getPublic();

        config(['signed_urls.private_keys.default' => $this->privateKey]);
        config(['signed_urls.public_keys.default' => $this->publicKey]);

        $this->service = new SignedUrlService(
            'testapp',
            new StaticCacheBroker,
            new ConfigKeyProvider
        );

    }
}
