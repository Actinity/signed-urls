<?php
namespace Actinity\SignedUrls;

use Actinity\SignedUrls\Exceptions\EncryptionError;

class PayloadEncrypter
{
    public static function decrypt(string $encryptedPayload, string $privateKey, ?string $publicKey = null): string
    {
        $payload = base64_decode($encryptedPayload);

        $payload = unserialize($payload);

        openssl_private_decrypt(
            $payload['key'],
            $decryptedKey,
            KeyFormatter::privateFromString($privateKey)
        );

        $decrypted = openssl_decrypt(
            $payload['payload'],
            $payload['cipher'] ?? static::getCipher(),
            $decryptedKey,
            0,
            $payload['iv'],
            $payload['tag']
        );

        static::validateSignature($decrypted,$payload['signature'] ?? null,$publicKey);

        return $decrypted;
    }

    private static function validateSignature(string $payload, ?string $signature, ?string $publicKey): void
    {
        if($signature) {
            if(!$publicKey) {
                throw new EncryptionError('Payload is signed but no public key was provided');
            }

            if(!openssl_verify(
                $payload,
                $signature,
                KeyFormatter::publicFromString($publicKey),
                OPENSSL_ALGO_SHA256
            )) {
                throw new EncryptionError('Payload signature is invalid');
            }
        }
    }

    public static function encrypt(string $payload, string $publicKey): string
    {
        $data = self::encryptToArray($payload,$publicKey);
        return base64_encode(serialize($data));
    }

    public static function encryptAndSign(string $payload, string $publicKey, string $privateKey): string
    {
        openssl_sign(
            $payload,
            $signature,
            KeyFormatter::privateFromString($privateKey),
            OPENSSL_ALGO_SHA256
        );

        return base64_encode(serialize(static::encryptToArray($payload,$publicKey,$signature)));
    }

    private static function getCipher()
    {
        return 'aes-128-gcm';
    }

    private static function encryptToArray(string $payload, string $publicKey, ?string $signature = null): array
    {
        $key = random_bytes(128);
        $iv = random_bytes(openssl_cipher_iv_length(static::getCipher()));

        // Encrypt the data with a symmetric key
        $encrypted_data = openssl_encrypt($payload,static::getCipher(),$key,0,$iv,$tag);

        // Now use the public key to encrypt the symmetric key
        openssl_public_encrypt($key,$encrypted_key,KeyFormatter::publicFromString($publicKey));

        return [
            'iv' => $iv,
            'tag' => $tag,
            'payload' => $encrypted_data,
            'key' => $encrypted_key,
            ...$signature ? ['signature' => $signature] : []
        ];
    }
}
