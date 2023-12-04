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
     * Test email.
     *
     * @var string
     */
    const TEST_EMAIL = 'api_test@example.com';

    /**
     * Execute the console command.
     */
    public function handle(WaApiProvider $waApiProvider): void
    {
        // Create contact.
        $start = microtime(true);
        $this->createContact($waApiProvider);
        $diff = round(microtime(true) - $start, 2).'s';
        $this->info("Execution time: $diff.");

        // Find contact.
        $start = microtime(true);
        $this->findContact($waApiProvider);
        $diff = round(microtime(true) - $start, 2).'s';
        $this->info("Execution time: $diff.");
    }

    /**
     * Gets the accounts.
     */
    protected function getAccounts(WaApiProvider $waApiProvider): void
    {
        $this->info('Test: Requesting Accounts from Wild Apricot API.');
        $data = $waApiProvider->get('accounts');
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
    }

    /**
     * Creates a sample contact.
     */
    protected function createContact(WaApiProvider $waApiProvider): void
    {
        $this->info('Test: Creating a contact in Wild Apricot API.');
        $data = $waApiProvider->createContact([
            'FirstName' => '_API_TEST_FIRST_NAME',
            'LastName' => '_API_TEST_LAST_NAME',
            'Email' => self::TEST_EMAIL,
        ]);
        if (empty($data)) {
            throw new \Exception('Could not create contact.');
        }
    }

    /**
     * Searches for the test contact.
     */
    protected function findContact(WaApiProvider $waApiProvider): array
    {
        $this->info('Test: Searching for a test contact.');
        $data = $waApiProvider->findContact(self::TEST_EMAIL);
        if (! empty($data)) {
            $this->info('Found contact.');
        } else {
            $this->info('Contact not found.');
        }

        return $data;
    }
}
