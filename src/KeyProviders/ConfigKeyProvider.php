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
        $key = config('signed_urls.keys.'.$keyName.'.private');
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
        $key = config('signed_urls.keys.'.$keyName.'.public');
        if (! $key) {
            throw new PublicKeyNotFound;
        }

        return $key;
    }
}
