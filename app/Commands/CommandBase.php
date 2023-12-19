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

    /**
     * Gets the existing WA membership bundles.
     *
     * @param  WaApiProvider  $waApiProvider
     *   Wild Apricot API provider.
     * @param  string  $levelId
     *   Id of the level to get the bundles for.
     * @return array
     *  Existing membership bundles.
     */
    protected function getMembershipBundles($waApiProvider, $levelId): array
    {
        $cached_file_name = \storage_path().'/membership_bundles_cache.json';
        if (self::ALLOW_CACHED && file_exists($cached_file_name)) {
            $items = json_decode(file_get_contents($cached_file_name), true);
            if (! empty($items)) {
                $this->info('Loaded cached membership bundles.');

                return $items;
            }
        }
        $this->info('Requesting existing membership bundles. Can take some time.');
        $items = $waApiProvider->getMembershipBundles();
        if (empty($items)) {
            throw new \Exception('Could not get membership bundles.');
        }
        $this->info('Received '.count($items).' membership bundles.');
        file_put_contents($cached_file_name, json_encode($items, JSON_PRETTY_PRINT));

        return $items;
    }

    /**
     * Gets the existing WA membership levels.
     *
     * @param  WaApiProvider  $waApiProvider
     *   Wild Apricot API provider.
     * @return array
     *  Existing membership levels.
     */
    protected function getMembershipLevels($waApiProvider): array
    {
        $cached_file_name = \storage_path().'/membership_levels_cache.json';
        if (self::ALLOW_CACHED && file_exists($cached_file_name)) {
            $items = json_decode(file_get_contents($cached_file_name), true);
            if (! empty($items)) {
                $this->info('Loaded cached membership levels.');

                return $items;
            }
        }
        $this->info('Requesting existing membership levels. Can take some time.');
        $items = $waApiProvider->getMembershipLevels();
        if (empty($items)) {
            throw new \Exception('Could not get membership levels.');
        }
        $this->info('Received '.count($items).' membership levels.');
        file_put_contents($cached_file_name, json_encode($items, JSON_PRETTY_PRINT));

        return $items;
    }
}
