<?php

namespace Actinity\SignedUrls;

readonly class GeneratedKeyPair
{
    private string $privateKey;

    private string $publicKey;

    public function __construct()
    {
        $raw = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($raw, $privateKey);
        $this->privateKey = $privateKey;
        $this->publicKey = openssl_pkey_get_details($raw)['key'];

    }

    public function getPrivate(bool $asString = false): string
    {
        return $asString ? KeyFormatter::toString($this->privateKey) : $this->privateKey;
    }

    public function getPublic(bool $asString = false): string
    {
        return $asString ? KeyFormatter::toString($this->publicKey) : $this->publicKey;
    }
}
