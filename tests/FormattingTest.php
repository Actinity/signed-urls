<?php

namespace Actinity\SignedUrlTests;

use Actinity\SignedUrls\KeyFormatter;

class FormattingTest extends TestCase
{
    public function test_a_public_key_works()
    {
        $key = $this->getResource('dummy_public.txt');

        $this->assertEquals($key, KeyFormatter::publicFromString($key));
    }

    public function test_a_public_key_string_works()
    {
        $key = $this->getResource('dummy_public.txt');
        $string = $this->getResource('dummy_public_string.txt');

        $this->assertEquals($key, KeyFormatter::publicFromString($string));
    }

    public function test_a_private_key_works()
    {
        $key = $this->getResource('dummy_private.txt');

        $this->assertEquals($key, KeyFormatter::privateFromString($key));
    }

    public function test_a_private_key_string_works()
    {
        $key = $this->getResource('dummy_private.txt');
        $naked = $this->getResource('dummy_private_string.txt');

        $this->assertEquals($key, KeyFormatter::privateFromString($naked));
    }
}
