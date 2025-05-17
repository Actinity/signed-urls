<?php

namespace Actinity\SignedUrlTests;

use Actinity\SignedUrls\Exceptions\InvalidSignedUrl;
use Actinity\SignedUrls\GeneratedKeyPair;

class ValidationTest extends SigningCase
{
    public function test_validating_with_nothing_triggers_an_exception()
    {
        $this->expectException(InvalidSignedUrl::class);
        $this->service->validate('https://example.com', '', '');
    }

    public function test_all_parameters_cannot_be_missing()
    {
        $this->expectException(InvalidSignedUrl::class);
        $this->service->validate('https://example.com');
        $this->assertCount(4, $this->getExpectedException()->errors());
    }

    public function test_some_parameters_cannot_be_missing()
    {
        $this->expectException(InvalidSignedUrl::class);

        $this->service->validate('https://example.com?ac_sg=1&ac_ts=444');

        $this->assertCount(2, $this->getExpectedException()->errors());
        $this->assertArrayHasKey('ac_nc', $this->getExpectedException()->errors());
    }

    public function test_tampering_works()
    {
        $url = $this->tamperWithSignedUrl(['ac_sg' => 'tampered_sig']);
        $this->assertEquals(1, substr_count($url, 'ac_sg=tampered_sig'));
    }

    public function test_a_timestamp_in_the_far_future_fails()
    {
        $this->expectException(InvalidSignedUrl::class);
        $url = $this->tamperWithSignedUrl(['ac_ts' => time() + 400]);
        $this->service->validate($url);
        $this->assertArrayHasKey('ac_ts', $this->getExpectedException()->errors());
    }

    public function test_a_timestamp_in_the_far_past_fails()
    {
        $url = $this->tamperWithSignedUrl(['ac_ts' => time() - 400]);
        $this->expectException(InvalidSignedUrl::class);

        $this->service->validate($url);

        $this->assertArrayHasKey('ac_ts', $this->getExpectedException()->errors());
    }

    public function test_a_timestamp_slightly_off_is_okay()
    {
        $this->expectNotToPerformAssertions();
        $url = $this->service->sign('https://example.com');
        sleep(1);
        $this->service->validate($url);
    }

    public function test_a_signed_url_cannot_be_reused()
    {
        $url = $this->service->sign('https://example.com');

        $this->assertTrue($this->service->validate($url));

        $this->expectException(InvalidSignedUrl::class);
        $this->service->validate($url);
    }

    public function test_a_tampered_signature_causes_a_failure()
    {
        $url = $this->tamperWithSignedUrl(['ac_sg' => 'tampered']);
        $this->expectException(InvalidSignedUrl::class);

        $this->service->validate($url);

        $this->assertArrayHasKey('ac_sg', $this->getExpectedException()->errors());
    }

    public function test_tampering_with_the_timestamp_causes_a_failure()
    {
        $this->expectException(InvalidSignedUrl::class);

        $url = $this->tamperWithSignedUrl(['ac_ts' => time() - 2]);
        $this->service->validate($url);
    }

    public function test_any_other_tampering_causes_a_failure()
    {
        $this->expectException(InvalidSignedUrl::class);

        $url = $this->tamperWithSignedUrl(['foo' => 'bar']);
        $this->service->validate($url);
    }

    public function test_a_url_within_expiry_is_valid()
    {
        $this->expectNotToPerformAssertions();
        $url = $this->service->make('https://example.com')
            ->withExpiry(time() + 300)
            ->get();
        $this->service->validate($url);
    }

    public function test_an_expired_url_is_invalid()
    {
        $this->expectException(InvalidSignedUrl::class);

        $url = $this->service->make('https://example.com')->forceExpiry(time() - 300)->get();

        $this->service->validate($url);
    }

    public function test_a_parameter_can_be_added()
    {
        $this->expectNotToPerformAssertions();
        $url = $this->service->make('https://example.com?foo=bar')->withParameter('bar', 'foo')->get();

        $this->service->validate($url);
    }

    public function test_a_url_cannot_be_changed()
    {
        $this->expectException(InvalidSignedUrl::class);

        $url = $this->service->make('https://example.com');

        $url = str_replace('example.com', 'different-example.com', $url);

        $this->service->validate($url);

    }

    public function test_a_scheme_cannot_be_changed()
    {
        $this->expectException(InvalidSignedUrl::class);

        $url = $this->service->make('https://example.com');

        $url = 'http'.substr($url, 5);

        $this->service->validate($url);

    }

    public function test_a_path_cannot_be_changed()
    {
        $this->expectException(InvalidSignedUrl::class);

        $url = $this->service->make('https://example.com/path-to/my-folder');

        $url = str_replace('/path-to/my-folder', '/different-path', $url);

        $this->service->validate($url);

    }

    public function test_fully_manual_keys()
    {
        $keyPair = new GeneratedKeyPair;

        $url = $this->service->make('https://example.com')
            ->withKey($keyPair->getPrivate())
            ->get();

        $this->assertTrue($this->service->validateWithPublicKey($url, $keyPair->getPublic()));

        // And with the default key to make sure
        $this->expectException(InvalidSignedUrl::class);
        $this->service->validate($url);

    }

    public function test_a_custom_source_can_be_used()
    {
        $this->expectNotToPerformAssertions();
        $keyPair = new GeneratedKeyPair;
        config(['signed_urls.public_keys.other_source' => $keyPair->getPublic()]);

        $url = $this->service->make('https://example.com')
            ->withSource('other_source')
            ->withKey($keyPair->getPrivate())
            ->get();

        $this->service->validate($url);
    }

    public function test_a_source_name_can_be_enforced()
    {
        $this->expectNotToPerformAssertions();
        $url = $this->service->make('https://example.com')
            ->withSource('other_source')
            ->get();

        $this->service->validate($url, 'default');
    }

    /**
     * @return string
     */
    private function tamperWithSignedUrl(array $tamper)
    {
        $base = 'https://example.com';
        $signed = $this->service->sign($base);
        parse_str(parse_url($signed)['query'], $params);

        $params = array_merge($params, $tamper);

        return $base.'?'.http_build_query($params);

    }
}
