<?php

namespace Actinity\SignedUrls;

use Actinity\SignedUrls\Contracts\CacheBroker;
use Actinity\SignedUrls\Contracts\KeyProvider;
use Actinity\SignedUrls\Exceptions\InvalidSignedUrl;
use Actinity\SignedUrls\Exceptions\PrivateKeyNotFound;

class SignedUrlService
{
    public function __construct(private string $sourceName, private CacheBroker $cacheBroker, private KeyProvider $keyProvider) {}

    /**
     * @throws PrivateKeyNotFound
     */
    public function sign(string $url): string
    {
        return $this->make($url)->get();
    }

    public function make(string $url): SignedUrl
    {
        return SignedUrl::create($url)
            ->withSource($this->sourceName)
            ->withKeyProvider($this->keyProvider);
    }

    /**
     * @throws InvalidSignedUrl
     */
    public function validate(string $url, ?string $keyName = null): bool
    {
        [$url_parts, $params] = $this->splitUrl($url);

        $this->validateParams($params);

        if (! $keyName) {
            $keyName = $params['ac_sc'] === $this->sourceName ? 'default' : $params['ac_sc'];
        }

        $key = $this->keyProvider->getPublicKey($keyName);

        $this->validateSignature($url_parts, $params, $key);

        return true;
    }

    /**
     * @throws InvalidSignedUrl
     */
    public function validateWithPublicKey(string $url, string $publicKey): bool
    {
        [$url_parts, $params] = $this->splitUrl($url);
        $this->validateParams($params);
        $this->validateSignature($url_parts, $params, $publicKey);

        return true;
    }

    private function validateSignature(array $url_parts, array $params, string $key): void
    {
        $signature = $params['ac_sg'];
        unset($params['ac_sg']);
        ksort($params);

        $valid = openssl_verify(
            static::reconstituteUrl($url_parts, $params),
            base64_decode($signature),
            KeyFormatter::publicFromString($key),
            OPENSSL_ALGO_SHA256
        );

        if (! $valid) {
            throw new InvalidSignedUrl(['ac_sg' => 'Signature is invalid']);
        }
    }

    private function validateParams(array $params): bool
    {
        // Check timestamp

        if (! is_numeric($params['ac_ts']) || $params['ac_ts'] > time() + 120) {
            throw new InvalidSignedUrl(['ac_ts' => 'Timestamp is invalid']);
        }
        // Check expiry

        if (! is_numeric($params['ac_xp']) || $params['ac_xp'] < time()) {
            throw new InvalidSignedUrl(['ac_xp' => 'URL has expired']);
        }

        // Check nonce has not been used
        $nonce_key = implode('|', ['signed-urls-signed-nonce', $params['ac_sc'], $params['ac_ts'], $params['ac_nc']]);

        if (! $this->cacheBroker->setNx($nonce_key, 1, $params['ac_xp'] - time())) {
            throw new InvalidSignedUrl(['ac_nc' => 'Nonce for '.$params['ac_sc'].' has been used already']);
        }

        return true;
    }

    private function splitUrl(string $url): array
    {
        if (! trim($url)) {
            throw new InvalidSignedUrl(['url' => 'URL is missing']);
        }

        $errors = [
            'ac_ts' => 'Timestamp is missing',
            'ac_nc' => 'Nonce is missing',
            'ac_sg' => 'Signature is missing',
            'ac_sc' => 'Source identifier is missing',
            'ac_xp' => 'Expiry is missing',
        ];

        $url_parts = parse_url($url);
        $url_parts['scheme'] = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $url_parts['scheme'];

        $query = $url_parts['query'] ?? null;

        if (! $query) {
            throw new InvalidSignedUrl($errors);
        }

        parse_str($query, $params);
        $errors = array_diff_key($errors, $params);

        if (count($errors)) {
            throw new InvalidSignedUrl($errors);
        }

        parse_str($query, $params);

        $errors = array_diff_key($errors, $params);

        if (count($errors)) {
            throw new InvalidSignedUrl($errors);
        }

        return [$url_parts, $params];
    }

    public static function reconstituteUrl(array $parts, array $params)
    {
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'] ?? '';

        $url = "{$scheme}://{$host}{$port}{$path}";

        if (count($params)) {
            $url .= '?'.http_build_query($params);
        }

        return $url;
    }
}
