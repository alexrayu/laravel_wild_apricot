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
        $existing_contacts = $this->getRemoteContacts($waApiProvider);
        $csv_contacts = $csvProvider->getCSV(storage_path().'/contacts.csv');
        $a = 1;

    }

    /**
     * Gets the existing WA contacts.
     *
     * @param  WaApiProvider  $waApiProvider
     *   Wild Apricot API provider.
     * @return array
     *  Existing contacts.
     */
    protected function getRemoteContacts($waApiProvider): array
    {
        $this->info('Requesting existing contacts. Can take some time.');
        $data = $waApiProvider->getContacts();
        if (empty($data['Contacts'])) {
            throw new \Exception('Could not get contacts.');
        }
        $contacts = [];
        foreach ($data['Contacts'] as $contact) {
            $contacts[$contact['Id']] = $contact;
        }
        $this->info('Received '.count($contacts).' contacts.');

        return $contacts;
    }
}
