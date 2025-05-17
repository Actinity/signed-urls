# Signed Urls

A library for signing and validating short-lived, one-time URLs 
using public and private keys. Designed for Laravel, but can 
be used elsewhere.

Evolved from `twogether/laravel-url-signer`


## Installation with Laravel

`composer require actinity/signed-urls`

`php artisan vendor:publish --provider="Actinity\SignedUrls\Laravel\SignedUrlServiceProvider"`

`php artisan actinity:generate-key-pair`

The last command will give you a public and private
key pair. Add the private key to your .env file.

```dotenv
SIGNED_URLS_PRIVATE_KEY=<PRIVATE_KEY>
```

## Configuring

Every app that you will be communicating between needs a `source_name`. 
By default this is `Str::slug(config('app.name'))` but you can 
explicitly set it in `config/signed_urls.php`.

Also in that config file you'll see a `public_keys` array.
This is where you can add the public keys for apps that will
make requests. So if you have an app called `dashboard`, you would
configure `'source_name' =>  'dashboard'`. If that needs to make
signed requests to an app called `reports`, then in that reports app
you would configure it like this:

```php
    'source_name' => 'reports',
    'public_keys' => [
        'dashboard' => <DASHBOARD_PUBLIC_KEY_HERE>
    ]
```

The `source_name` parameter from your config will be added to every
generated URL as `ac_sc`. For more complex logic when resolving a key
to validate, you can implement `Actinity\SignedUrls\Contracts\KeyProvider`
and specify that in the config.


## Generating URLs
To generate a signed URL, get the service from the 
service container, likely through injection.

```php
$service = app(\Actinity\SignedUrls\SignedUrlService::class);
$url = $service->sign('https://www.example.com');
```

## Validating URLs

To validate a signed URL:

```php
$service = app(\Actinity\SignedUrls\SignedUrlService::class);
try {
    $service->validate($request->fullUrl());
} catch(\Actinity\SignedUrls\Exceptions\InvalidSignedUrl $exception) {
    dd($exception->errors());
}
```

You can also just use `Actinity\SignedUrls\Laravel\SignedUrlMiddleware`.