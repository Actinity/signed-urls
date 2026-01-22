<?php

namespace Actinity\SignedUrls\KeyProviders;

use Actinity\SignedUrls\Contracts\KeyProvider;
use Actinity\SignedUrls\Exceptions\PrivateKeyNotFound;
use Actinity\SignedUrls\Exceptions\PublicKeyNotFound;

class ArrayKeyProvider implements KeyProvider
{
    private $keys;

    public function __construct(array $keys)
    {
        $this->keys = $keys;
    }

    /**
     * @throws PrivateKeyNotFound
     */
    public function getPrivateKey(string $keyName = 'default'): string
    {
        if (array_key_exists('private', $this->keys) && ($this->keys['private'][$keyName] ?? false)) {
            return $this->keys['private'][$keyName];
        }
        throw new PrivateKeyNotFound;
    }

    /**
     * @throws PublicKeyNotFound
     */
    public function getPublicKey(string $keyName = 'default'): string
    {
        if (array_key_exists($keyName, $this->keys) && ($this->keys['public'][$keyName] ?? false)) {
            return $this->keys['public'][$keyName];
        }
        throw new PublicKeyNotFound;
    }
}
