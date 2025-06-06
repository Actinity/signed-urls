<?php

namespace Actinity\SignedUrls\Laravel;

use Actinity\SignedUrls\GeneratedKeyPair;
use Illuminate\Console\Command;

class GenerateKeyPairCommand extends Command
{
    protected $signature = 'actinity:generate-key-pair {--raw}';

    protected $description = 'Generate a public/private key pair for URL signing';

    public function handle(): int
    {
        $pair = new GeneratedKeyPair;

        $this->warn('PRIVATE KEY');
        $this->info($pair->getPrivate(! $this->option('raw')));

        $this->warn('PUBLIC KEY');
        $this->info($pair->getPublic(! $this->option('raw')));

        return self::SUCCESS;
    }
}
