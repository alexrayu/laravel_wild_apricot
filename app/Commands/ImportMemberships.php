<?php

namespace App\Commands;

use App\Providers\CsvProvider;
use App\Providers\WaApiProvider;

/**
 * Imports the contacts.
 */
class ImportMemberships extends CommandBase
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:import_memberships';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Imports memberships to Wild Apricot.';

    /**
     * Whether to allow loading cached values instead of requesting them.
     *
     * @var bool
     */
    const ALLOW_CACHED = true;

    /**
     * Execute the console command.
     */
    public function handle(WaApiProvider $waApiProvider, CsvProvider $csvProvider): void
    {
        $contacts = $this->getRemoteContacts($waApiProvider);
        $memberships = $csvProvider->getCSV(\storage_path().'/memberships.csv')['data'];
        $matched_memberships = $this->matchRecords($contacts, $memberships);
    }

    /**
     * Matches the records (contacts to memberships).
     *
     * @param  array  $contacts
     *   Existing contacts.
     * @param  array  $memberships
     *   CSV memberships.
     * @return array
     *   Matched memberships with contact ids added.
     */
    protected function matchRecords(array $contacts, array $memberships): array
    {
        $matched_contacts = [];
        foreach ($contacts as $csv_id => $csv_contact) {

            $contact_full_name = trim(strtolower($csv_contact['FirstName'].' '.$csv_contact['LastName']));
            if (empty($contact_full_name)) {
                continue;
            }
            foreach ($memberships as $membership) {
                if ($contact_full_name === trim(strtolower($membership['full_name']))) {
                    $matched_contacts[$csv_id][] = $membership;

                    continue 2;
                }
            }
        }
        $this->info('Matched '.count($matched_contacts).' contacts.');

        return $matched_contacts;
    }

}
