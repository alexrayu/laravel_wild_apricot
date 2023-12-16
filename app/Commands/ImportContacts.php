<?php

namespace App\Commands;

use App\Providers\CsvProvider;
use App\Providers\WaApiProvider;

/**
 * Imports the contacts.
 */
class ImportContacts extends CommandBase
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
        $csv_contacts = $csvProvider->getCSV(storage_path().'/contacts.csv')['data'];
        $matched_contacts = $this->matchContacts($existing_contacts, $csv_contacts);
    }

    /**
     * Matches the contacts.
     *
     * @param  array  $existing_contacts
     *   Existing contacts.
     * @param  array  $csv_contacts
     *   CSV contacts.
     * @return array
     *   Matched contacts.
     */
    protected function matchContacts(array $existing_contacts, array $csv_contacts): array
    {
        $matched_contacts = [];
        foreach ($csv_contacts as $csv_id => $csv_contact) {
            $csv_email = strtolower($csv_contact['email']);
            foreach ($existing_contacts as $existing_contact) {
                $api_email = strtolower($existing_contact['FieldValues']['email_address']['Value']);
                if ($api_email === $csv_email) {
                    $matched_contacts[$existing_contact['Id']] = $csv_id;

                    continue 2;
                }
            }
        }
        $this->info('Matched '.count($matched_contacts).' contacts.');

        return $matched_contacts;
    }
}
