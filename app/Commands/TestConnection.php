<?php

namespace App\Commands;

use App\Providers\WaApiProvider;
use LaravelZero\Framework\Commands\Command;

/**
 * Checks the connection to the apis.
 */
class TestConnection extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:test_connection';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Tests connection to Wild Apricot.';

    /**
     * Execute the console command.
     */
    public function handle(WaApiProvider $waApiProvider): void
    {
        $this->info($waApiProvider->test());
    }
}
