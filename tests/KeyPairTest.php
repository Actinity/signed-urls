<?php

namespace Actinity\SignedUrlTests;

use Actinity\SignedUrls\GeneratedKeyPair;

class KeyPairTest extends TestCase
{
    public function test_a_valid_pair_is_generated()
    {
        $pair = new GeneratedKeyPair;

        openssl_sign('test string', $signature, $pair->getPrivate());
        $this->assertSame(1, openssl_verify('test string', $signature, $pair->getPublic()));
    }
}
