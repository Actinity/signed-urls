<?php

namespace Actinity\SignedUrls\KeyProviders;

use Actinity\SignedUrls\Contracts\KeyProvider;
use Actinity\SignedUrls\Exceptions\PrivateKeyNotFound;
use Actinity\SignedUrls\Exceptions\PublicKeyNotFound;

class DummyKeyProvider implements KeyProvider
{
    /**
     * @throws PrivateKeyNotFound
     */
    public function getPrivateKey(string $keyName = 'default'): string
    {
        throw new PrivateKeyNotFound;
    }

    /**
     * @throws PublicKeyNotFound
     */
    public function getPublicKey(string $keyName = 'default'): string
    {
        throw new PublicKeyNotFound;
    }
}
