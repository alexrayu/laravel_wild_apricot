<?php

namespace App\Commands;

use App\Providers\WaApiProvider;
use LaravelZero\Framework\Commands\Command;

/**
 * Imports the contacts.
 */
class CommandBase extends Command
{

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:base';

    /**
     * Whether to allow loading cached values instead of requesting them.
     *
     * @var bool
     */
    const ALLOW_CACHED = true;

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
        $cached_file_name = \storage_path().'/contacts_cache.json';
        if (self::ALLOW_CACHED && file_exists($cached_file_name)) {
            $contacts = json_decode(file_get_contents($cached_file_name), true);
            if (! empty($contacts)) {
                $this->info('Loaded cached contacts.');

                return $contacts;
            }
        }
        $this->info('Requesting existing contacts. Can take some time.');
        $contacts = $waApiProvider->getContacts();
        if (empty($contacts)) {
            throw new \Exception('Could not get contacts.');
        }
        $this->info('Received '.count($contacts).' contacts.');
        file_put_contents($cached_file_name, json_encode($contacts));

        return $contacts;
    }
}
