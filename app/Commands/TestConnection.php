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
        $start = microtime(true);
        $this->info('Test: Requesting Accounts from Wild Apricot API.');
        $data = $waApiProvider->get('accounts');
        $diff = round(microtime(true) - $start, 2) . 's';
        if (empty($data)) {
            throw new \Exception('Could not get accounts.');
        }

        $accounts = [];
        foreach ($data as $account) {
            $accounts[$account['Id']] = [
                'id' => $account['Id'],
                'name' => $account['Name'],
            ];
        }
        $this->table(['ID', 'Name'], $accounts);
        $this->info("Execution time: $diff.");
    }
}
