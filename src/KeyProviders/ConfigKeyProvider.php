<?php

namespace Actinity\SignedUrls\KeyProviders;

use Actinity\SignedUrls\Contracts\KeyProvider;
use Actinity\SignedUrls\Exceptions\PrivateKeyNotFound;
use Actinity\SignedUrls\Exceptions\PublicKeyNotFound;

class ConfigKeyProvider implements KeyProvider
{
    /**
     * @throws PrivateKeyNotFound
     */
    public function getPrivateKey(string $keyName = 'default'): string
    {
        $key = config('signed_urls.private_keys.'.$keyName);
        if (! $key) {
            throw new PrivateKeyNotFound;
        }

        return $key;
    }

    /**
     * @throws PublicKeyNotFound
     */
    public function getPublicKey(string $keyName = 'default'): string
    {
        $key = config('signed_urls.public_keys.'.$keyName);
        if (! $key) {
            throw new PublicKeyNotFound;
        }

        return $key;
    }
}
