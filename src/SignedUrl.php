<?php

namespace Actinity\SignedUrls;

use Actinity\SignedUrls\Contracts\KeyProvider;
use Actinity\SignedUrls\Exceptions\InvalidUrl;
use Actinity\SignedUrls\Exceptions\PrivateKeyNotFound;
use Hidehalo\Nanoid\Client;

class SignedUrl
{
    private int $expiry;

    private string $key;

    private string $sourceName;

    private string $keyName = 'default';

    private KeyProvider $keyProvider;

    private $parameters = [];

    public function __construct(private string $url) {}

    public static function create(string $url): self
    {
        return new self($url);
    }

    public function withKeyProvider(KeyProvider $keyProvider): self
    {
        $this->keyProvider = $keyProvider;

        return $this;
    }

    public function withKeyName(string $keyName): self
    {
        $this->keyName = $keyName;

        return $this;
    }

    public function withKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function withSource(string $sourceName): self
    {
        $this->sourceName = $sourceName;

        return $this;
    }

    public function withExpiry(int $seconds): self
    {
        if ($seconds <= time()) {
            throw new \InvalidArgumentException('Expiry must be in the future');
        }
        $this->expiry = $seconds;

        return $this;
    }

    public function forceExpiry(int $seconds): self
    {
        $this->expiry = $seconds;

        return $this;
    }

    public function withParameters(array $parameters): self
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    public function withParameter(string $key, string $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function __toString(): string
    {
        return $this->get();
    }

    public function get(): string
    {
        $parts = parse_url($this->url);

        if (! array_key_exists('scheme', $parts) || ! array_key_exists('host', $parts)) {
            throw new InvalidUrl;
        }

        parse_str($parts['query'] ?? '', $args);

        $args = array_merge($args, $this->parameters);

        $args['ac_xp'] = $this->expiry ?? time() + 120;
        $args['ac_ts'] = time();
        $args['ac_nc'] = (new Client)->generateId();
        $args['ac_sc'] = $this->sourceName;

        ksort($args);

        $key = $this->getKey();
        $url = SignedUrlService::reconstituteUrl($parts, $args);

        openssl_sign(
            $url,
            $signature,
            KeyFormatter::privateFromString($key),
            OPENSSL_ALGO_SHA256
        );

        $args['ac_sg'] = base64_encode($signature);

        return SignedUrlService::reconstituteUrl($parts, $args);
    }

    /**
     * @throws PrivateKeyNotFound
     */
    private function getKey(): string
    {
        if (isset($this->key)) {
            return $this->key;
        }

        if (! isset($this->keyProvider)) {
            throw new \Exception('No Key, and no Key Provider specified');
        }

        return $this->keyProvider->getPrivateKey($this->keyName);

    }
}
