<?php

/**
 * Manage keys for the Actinity/SignedUrls package.
 * Don't feel the need to load public and private keys if
 * you don't need them. You will likely only need either
 * one or the other unless you are both making and
 * receiving requests from this application.
 */

return [

    'source_name' => null, // defaults to Str::slug(config('app.name'))

    'key_provider' => \Actinity\SignedUrls\KeyProviders\ConfigKeyProvider::class,

    'private_keys' => [
        'default' => env('SIGNED_URLS_PRIVATE_KEY'),
    ],

    'public_keys' => [
        'default' => env('SIGNED_URLS_PUBLIC_KEY'),
    ],

];
