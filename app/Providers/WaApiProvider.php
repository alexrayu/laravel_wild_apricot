<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

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

    public function test(): string
    {
        $token = $this->getToken();

        return 'Test';
    }

    protected function getToken(): string
    {
        // Get the API key and secret from the app config.
        $uid = config('app.wa_api.uid');
        $apiKey = config('app.wa_api.key');
        $secret = config('app.wa_api.secret');
        $authUrl = config('app.wa_api.auth_url');

        $data = 'grant_type=client_credentials&scope=auto';
        $authorizationHeader = 'Basic ' . base64_encode('APIKEY:' . $apiKey);

        $authString = "?clientId=$apiKey&clientSecret=$secret";

        // Get the token from the API.
        $response = Http::withHeaders([
          'Authorization', $authorizationHeader,
          'Content-Length' => strlen($data),
        ])->get($authUrl . $authString, $data);

        // Return the token.
        $json = $response->json();
        return $response->json()['access_token'];
    }

  public function initTokenByContactCredentials($userName, $password, $scope = null)
  {
    if ($scope) {
      $this->tokenScope = $scope;
    }

    $this->token = $this->getAuthTokenByAdminCredentials($userName, $password);
    if (!$this->token) {
      throw new Exception('Unable to get authorization token.');
    }
  }
}
