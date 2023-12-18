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
        $membeship_bundles = $this->getMembershipBundles($waApiProvider);
        $membership_levels = $this->getMembershipLevels($waApiProvider);
        $matched_records = $this->matchRecords($contacts, $memberships);
        foreach ($matched_records as $contact_id => $memberships) {
            foreach ($memberships as $membership) {
                $a = 1;
            }
        }
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
        $matched_records = [];
        $matches_count = 0;
        foreach ($contacts as $csv_id => $csv_contact) {

            $contact_full_name = trim(strtolower($csv_contact['FirstName'].' '.$csv_contact['LastName']));
            if (empty($contact_full_name)) {
                continue;
            }
            foreach ($memberships as $membership) {
                if ($contact_full_name === trim(strtolower($membership['full_name']))) {
                    $matched_records[$csv_id][] = $membership;
                    $matches_count++;
                }
            }
        }
        $this->info('Matched '.count($matched_records).' contacts.');

        return $matched_records;
    }
}
