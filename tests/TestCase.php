<?php

namespace Actinity\SignedUrlTests;

use Actinity\SignedUrls\Laravel\SignedUrlServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getResource($name)
    {
        return trim(file_get_contents(__DIR__.'/resources/'.$name));
    }

    protected function getPackageProviders($app)
    {
        return [
            SignedUrlServiceProvider::class,
        ];
    }
}
