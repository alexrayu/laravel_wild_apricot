<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

/**
 * Wild Apricot API service provider.
 */
class WaApiProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WaApiProvider::class, function ($app) {
            return new WaApiProvider($app);
        });
    }

    /**
     * Get the accounts from the API.
     *
     * @return array
     *   The accounts.
     */
    public function getAccounts(): array
    {
        return $this->get('accounts');
    }

    /**
     * Get the contacts from the API.
     *
     * @return array
     *   The contacts.
     */
    public function getContacts(): array
    {
        $account_id = config('app.wa_api.account_id');
        $data = $this->get("accounts/$account_id/contacts", [
            '$async' => 'false',
        ]);

        $contacts = [];
        foreach ($data['Contacts'] as $contact) {

            // Process custom fields.
            $new_custom_fields = [];
            $field_names = [];
            foreach ($contact['FieldValues'] as $field_value) {
                $field_names[] = $field_value['FieldName'];
                $name = strtolower(str_replace(' ', '_', $field_value['FieldName']));
                $new_custom_fields[$name] = $field_value;
            }
            $contact['FieldValues'] = $new_custom_fields;
            $contacts[$contact['Id']] = $contact;
        }

        return $contacts;
    }

    /**
     * Get the data from the API.
     *
     * @param  string  $uri
     *   The URI to get.
     * @param  array  $params
     *   The parameters to pass.
     * @return array
     *   The data.
     */
    public function get($uri, $params = []): array
    {
        $token = $this->getToken();
        if (empty($token)) {
            $token = $this->getToken(true);
        }
        if (empty($token)) {
            throw new \Exception('Could not get token.');
        }
        $url = config('app.wa_api.url').$uri;
        $response = Http::withToken($token)->get($url, $params);
        if ($response->failed()) {
            $token = $this->getToken(true);
            if (empty($token)) {
                throw new \Exception('Could not get token.');
            }
            $response = Http::withToken($token)->get($url, $params);
            if ($response->failed()) {
                throw new \Exception($response->reason());
            }
        }

        return $response->json();
    }

    /**
     * Post data to the API.
     *
     * @param  string  $uri
     *   The URI to post to.
     * @param  array  $params
     *   The parameters to pass.
     * @return array
     *   The response data.
     */
    public function post($uri, $params = []): array
    {
        $token = $this->getToken();
        if (empty($token)) {
            $token = $this->getToken(true);
        }
        if (empty($token)) {
            throw new \Exception('Could not get token.');
        }
        $url = config('app.wa_api.url').$uri;
        $response = Http::withToken($token)->post($url, $params);
        if ($response->failed()) {
            $token = $this->getToken(true);
            if (empty($token)) {
                throw new \Exception('Could not get token.');
            }
            $response = Http::withToken($token)->post($url, $params);
            if ($response->failed()) {
                throw new \Exception($response->reason());
            }
        }

        return $response->json();
    }

    /**
     * Creates a contact at WA API.
     *
     * @param  array  $contact
     *   The contact to create.
     * @return array
     *   The response data containing the new contact.
     */
    public function createContact(array $contact): array
    {
        $account_id = config('app.wa_api.account_id');
        $response = $this->post("accounts/$account_id/contacts", [
            'contact' => json_encode($contact, JSON_FORCE_OBJECT),
        ]);

        if (empty($response['Id'])) {
            throw new \Exception('Could not create contact.');
        }

        return $response;
    }

    /**
     * Searches for a contact at WA API.
     *
     * @param  string  $email
     *   Finds contact by email.
     * @return array
     *   The response data containing the found contact if any.
     */
    public function findContact($email): array
    {
        $email = strtolower($email);
        $contacts = $this->getContacts();
        foreach ($contacts as $contact) {
            if (! empty($contact['Email']) && strtolower($contact['Email']) === $email
                || (! empty($contact['FieldValues']['email']['Value'])
                    && strtolower($contact['FieldValues']['email']['Value']) === $email)) {
                return $contact;
            }
        }

        return [];
    }

    /**
     * Get the token from the API.
     *
     * @param  bool  $renew
     *   Whether to renew the token.
     * @return string
     *   The token.
     */
    protected function getToken($renew = false): string
    {
        $token = file_get_contents(storage_path('token.key'));
        if ($token && ! $renew) {
            return $token;
        }

        // Get the API key and secret from the app config.
        $apiKey = config('app.wa_api.key');
        $authUrl = config('app.wa_api.auth_url');

        // Get the token from the API.
        $response = Http::withBasicAuth('APIKEY', $apiKey)
            ->post($authUrl, [
                'grant_type' => 'client_credentials',
                'scope' => 'auto',
            ]);

        // Return the token.
        $token = $response->json()['access_token'];
        if ($token) {
            file_put_contents(storage_path('token.key'), $token);

            return $token;
        }

        throw new \Exception('Could not get token.');
    }
}
