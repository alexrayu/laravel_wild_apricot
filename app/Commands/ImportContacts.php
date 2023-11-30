<?php

namespace App\Commands;

use App\Providers\CsvProvider;
use App\Providers\WaApiProvider;
use LaravelZero\Framework\Commands\Command;

/**
 * Imports the contacts.
 */
class ImportContacts extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:import_contacts';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Imports contacts to Wild Apricot.';

    /**
     * Execute the console command.
     */
    public function handle(WaApiProvider $waApiProvider, CsvProvider $csvProvider): void
    {
        $start = microtime(true);
        $this->info('Requesting existing contacts.');
        $data = $waApiProvider->getContacts();
        $diff = round(microtime(true) - $start, 2).'s';
        if (empty($data['Contacts'])) {
            throw new \Exception('Could not get contacts.');
        }
        $this->info("Query time: $diff.");
        $contacts = [];
        foreach ($data['Contacts'] as $contact) {
            $contacts[$contact['Id']] = $contact;
        }
        $this->info('Received '.count($contacts).' contacts.');
    }
}
