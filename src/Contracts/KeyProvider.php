<?php

namespace Actinity\SignedUrls\Contracts;

use Actinity\SignedUrls\Exceptions\PrivateKeyNotFound;
use Actinity\SignedUrls\Exceptions\PublicKeyNotFound;

interface KeyProvider
{
    /**
     * @throws PublicKeyNotFound
     */
    public function getPrivateKey(string $keyName = 'default'): string;

    /**
     * @throws PrivateKeyNotFound
     */
    public function getPublicKey(string $keyName = 'default'): string;
}
