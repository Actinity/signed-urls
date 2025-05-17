<?php

namespace Actinity\SignedUrlTests;

use Actinity\SignedUrls\Exceptions\PrivateKeyNotFound;
use Actinity\SignedUrls\Exceptions\PublicKeyNotFound;

class ConfigurationTest extends SigningCase
{
    public function test_a_missing_private_key_triggers_an_exception()
    {
        config(['signed_urls.private_keys.default' => '']);
        $this->expectException(PrivateKeyNotFound::class);
        $this->service->sign('https://example.com');
    }

    public function test_a_missing_public_key_triggers_an_exception()
    {
        config(['signed_urls.public_keys.default' => '']);
        $this->expectException(PublicKeyNotFound::class);
        $signed = $this->service->sign('https://example.com');
        $this->service->validate($signed);
    }

    public function test_an_alternative_key_can_be_used()
    {
        $this->expectNotToPerformAssertions();
        config(['signed_urls.private_keys.different' => $this->privateKey]);
        $this->service->make('https://example.com')
            ->withKeyName('different')
            ->get();
    }

    public function test_an_alternative_key_can_be_used_for_validation()
    {
        config(['signed_urls.public_keys.different' => $this->publicKey]);
        $signed = $this->service->make('https://example.com')->withSource('different')->get();
        $this->assertTrue($this->service->validate($signed, 'different'));
    }

    public function test_a_non_existent_key_triggers_exception()
    {
        $this->expectException(PrivateKeyNotFound::class);
        $this->service->make('https://example.com')
            ->withKeyName('non-existent')
            ->get();
    }

    public function test_a_non_existent_public_key_triggers_exception()
    {
        $this->expectException(PublicKeyNotFound::class);
        $signed = $this->service->sign('https://example.com');
        $this->assertFalse($this->service->validate($signed, 'non-existent'));
    }

    public function test_a_blank_keyname_triggers_an_exception()
    {
        $this->expectException(PrivateKeyNotFound::class);
        $this->service->make('https://example.com')
            ->withKeyName('')
            ->get();
    }
}
