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
        $membership_levels = $this->getMembershipLevels($waApiProvider);
        $matched_records = $this->matchRecords($contacts, $memberships, $membership_levels);
        foreach ($matched_records as $contact_id => $membership) {
            $contact = $contacts[$contact_id];
            $this->addContactToMembership($waApiProvider, $contact, $membership);
        }
    }

    /**
     * Updates a contact with a membership.
     *
     * @param  WaApiProvider  $waApiProvider
     *   Wild Apricot API provider.
     * @param  array  $contact
     *   Contact.
     * @param  array  $membership
     *   Membership.
     */
    protected function addContactToMembership($waApiProvider, $contact, $membership) {

    }

    /**
     * Matches the records (contacts to memberships).
     *
     * @param  array  $contacts
     *   API contacts.
     * @param  array  $memberships
     *   CSV memberships.
     * @param  array  $membership_levels
     *   API membership levels.
     * @return array
     *   Matched memberships with contact ids added.
     */
    protected function matchRecords(array $contacts, array $memberships, array $membership_levels): array
    {
        $matched_records = [];

        // Match memberships to contacts by Full Name.
        foreach ($contacts as $contact_id => $csv_contact) {
            $contact_full_name = trim(strtolower($csv_contact['FirstName'].' '.$csv_contact['LastName']));
            if (empty($contact_full_name)) {
                continue;
            }
            foreach ($memberships as $membership) {
                if ($contact_full_name === trim(strtolower($membership['full_name']))) {
                    $membership['contact_id'] = $contact_id;
                    $matched_records[$contact_id] = $membership;
                }
            }
        }
        $this->info('Matched '.count($matched_records).' contacts.');

        // Match membership Levels to contacts by membership level name.
        $matched_levels = 0;
        foreach ($matched_records as $contact_id => $memberships) {
            foreach ($membership_levels as $membership_level) {
                if ($memberships['membership'] === $membership_level['Name']) {
                    $matched_records[$contact_id]['_membership_level'] = $membership_level;
                    $matched_levels++;
                    continue 2;
                }
            }
        }

        return $matched_records;
    }
}
