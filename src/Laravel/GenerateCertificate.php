<?php

namespace Actinity\SignedUrls\Laravel;

use Actinity\SignedUrls\KeyFormatter;
use Illuminate\Console\Command;

class GenerateCertificate extends Command
{
    protected $signature = 'actinity:generate-certificate {privateKey?} {--expiresInDays=3650} {--organizationName=} {--commonName=}';

    protected $description = 'Generate a Certificate for SAML';

    public function handle(): int
    {
        $privateKey = $this->argument('privateKey');
        $expiresInDays = (int) $this->option('expiresInDays');

        if(!$expiresInDays) {
            $this->error('Expires in days must be a positive integer.');
            return 1;
        }

        if (! $privateKey) {
            $privateKey = config('signed_urls.private_keys.default');
        }

        if(!$privateKey) {
            $this->error('No private key found. Configure signed_urls.private_keys.default or provide one');
            return 1;
        }

        $privateKey = KeyFormatter::privateFromString($privateKey);
        $privateKey = openssl_pkey_get_private($privateKey);

        $config = [
            'commonName' => $this->option('commonName') ?: config('app.url'),
            'organizationName' => $this->option('organizationName') ?: config('app.name'),
        ];


        $csr = openssl_csr_new($config, $privateKey);
        $cert = openssl_csr_sign($csr, null, $privateKey, $expiresInDays);

        openssl_x509_export($cert, $cert);

        $this->warn('CERTIFICATE');
        $this->info($cert);


        $this->warn('CERTIFICATE AS STRING');
        $this->info(KeyFormatter::toString($cert));

        $this->warn('CERTIFICATE CONFIGURATION');
        $this->info(json_encode([...$config,'expiresInDays' => $expiresInDays],JSON_PRETTY_PRINT));

        return 0;
    }
}