<?php

namespace Actinity\SignedUrlTests;

use Actinity\SignedUrls\Exceptions\InvalidUrl;

class SigningTest extends SigningCase
{
    public function test_a_url_with_just_host_works()
    {
        $url = 'https://example.com';

        $signed = $this->service->sign($url);

        $this->assertEquals($url,
            substr($signed, 0, strlen($url))
        );
    }

    public function test_an_invalid_url_throws_an_exception()
    {
        $this->expectException(InvalidUrl::class);
        $this->service->sign('/');
    }

    public function test_an_empty_url_throws_an_exception()
    {
        $this->expectException(InvalidUrl::class);
        $this->service->sign('');
    }

    public function test_parameters_are_present()
    {
        $signed = $this->service->sign('https://example.com');

        parse_str(parse_url($signed)['query'], $params);

        $this->assertArrayHasKey('ac_nc', $params);
        $this->assertArrayHasKey('ac_sg', $params);
        $this->assertArrayHasKey('ac_ts', $params);
        $this->assertArrayHasKey('ac_xp', $params);

    }

    public function test_a_url_with_path_works()
    {
        $url = 'https://example.com/foo';

        $signed = $this->service->sign($url);

        $this->assertEquals($url,
            substr($signed, 0, strlen($url))
        );
    }

    public function test_an_http_url_works()
    {
        $url = 'http://example.com/foo';

        $signed = $this->service->sign($url);

        $this->assertEquals($url,
            substr($signed, 0, strlen($url))
        );
    }

    public function test_localhost_works()
    {
        $url = 'http://localhost/foo';

        $signed = $this->service->sign($url);

        $this->assertEquals($url,
            substr($signed, 0, strlen($url))
        );
    }

    public function test_a_port_can_be_preserved()
    {
        $url = 'http://localhost:8080/foo';

        $signed = $this->service->sign($url);

        $this->assertEquals($url,
            substr($signed, 0, strlen($url))
        );
    }

    public function test_query_parameters_are_preserved()
    {
        $url = 'https://example.com/foo?xyz=abc';
        $signed = $this->service->sign($url);

        parse_str(parse_url($signed)['query'], $params);

        $this->assertEquals('abc', $params['xyz']);
    }

    public function test_query_strings_are_not_doubled_up()
    {
        $root = 'https://example.com/foo';
        $url = $root.'?xyz=abc';

        $signed = $this->service->sign($url);

        $this->assertEquals($root,
            substr($signed, 0, strlen($root))
        );

        $this->assertEquals(1, substr_count($signed, '?'));
    }

    public function test_a_parameter_can_be_added()
    {
        $url = $this->service->make('https://example.com?foo=bar')->withParameter('bar', 'foo')->get();

        $parts = parse_url($url);
        parse_str($parts['query'], $query);

        $this->assertEquals('bar', $query['foo']);
        $this->assertEquals('foo', $query['bar']);
    }

    public function test_expiry_must_be_future()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->make('https://example.com')->withExpiry(time());
    }

    public function test_expiry_can_be_reduced()
    {
        $url = $this->service->make('https://example.com')->withExpiry(time() + 30)->get();

        $parts = parse_url($url);
        parse_str($parts['query'], $query);

        $this->assertLessThan(time() + 31, $query['ac_xp']);
    }

    public function test_expiry_can_be_increased()
    {
        $url = $this->service->make('https://example.com')->withExpiry(time() + 120)->get();

        $parts = parse_url($url);
        parse_str($parts['query'], $query);

        $this->assertGreaterThan(time() + 118, $query['ac_xp']);
    }
}
