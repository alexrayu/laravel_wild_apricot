<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

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
     * Get the data from the API.
     *
     * @param  string  $uri
     *   The URI to get.
     *
     * @return array
     *   The data.
     */
    public function get($uri): array
    {
        $token = $this->getToken();
        if (empty($token)) {
            $token = $this->getToken(true);
        }
        if (empty($token)) {
            throw new \Exception('Could not get token.');
        }
        $url = config('app.wa_api.url') . $uri;
        $response = Http::withToken($token)->get($url);
        if ($response->failed()) {
            $token = $this->getToken(true);
            if (empty($token)) {
                throw new \Exception('Could not get token.');
            }
            $response = Http::withToken($token)->get($url);
            if ($response->failed()) {
                throw new \Exception($response->reason());
            }
        }

        return $response->json();
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
