<?php

namespace Actinity\SignedUrlTests;

use Actinity\SignedUrls\GeneratedKeyPair;
use Actinity\SignedUrls\PayloadEncrypter;

class PayloadEncrypterTest extends TestCase
{
    public function test_a_payload_can_be_encrypted_and_decrypted()
    {
        $pair = new GeneratedKeyPair;

        $encrypted = PayloadEncrypter::encrypt('test string', $pair->getPublic());

        $this->assertNotEquals('test string', $encrypted);
        $this->assertEquals('test string', PayloadEncrypter::decrypt($encrypted, $pair->getPrivate()));
    }

    public function test_a_payload_can_be_encrypted_signed_and_decrypted()
    {
        $pair = new GeneratedKeyPair;

        $encrypted = PayloadEncrypter::encryptAndSign('test string', $pair->getPublic(), $pair->getPrivate());

        $this->assertEquals('test string', PayloadEncrypter::decrypt($encrypted, $pair->getPrivate(), $pair->getPublic()));
    }
}
