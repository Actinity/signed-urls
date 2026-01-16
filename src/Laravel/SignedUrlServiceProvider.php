<?php

namespace Actinity\SignedUrls\Laravel;

use Actinity\SignedUrls\KeyProviders\ConfigKeyProvider;
use Actinity\SignedUrls\SignedUrlService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class SignedUrlServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            GenerateKeyPairCommand::class,
            GenerateCertificate::class,
        ]);

        $this->publishes([
            __DIR__.'/config.php' => config_path('signed_urls.php'),
        ]);

        $this->app['router']->aliasMiddleware('signed_url', SignedUrlMiddleware::class);

    }

    public function register(): void
    {
        app()->singleton(
            SignedUrlService::class,
            function () {

                $keyProviderClass = config('signed_urls.key_provider') ?: ConfigKeyProvider::class;

                return new SignedUrlService(
                    sourceName: config('signed_urls.source_name', Str::slug(config('app.name'))),
                    cacheBroker: new LaravelCacheBroker,
                    keyProvider: new $keyProviderClass
                );
            }
        );
    }
}
