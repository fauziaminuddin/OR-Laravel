<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class OpenRemoteService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://202.10.41.74:8443', // Base URI for the OpenRemote API
            'verify' => false, // Only for local development, disable SSL verification
        ]);
    }

    public function refreshTokenIfNeeded()
    {
        $token = Cache::get('openremote_token');

        if (!$token) {
            $response = $this->client->post('/auth/realms/master/protocol/openid-connect/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => 'master', // Replace with your actual client_id
                    'client_secret' => 'FL3I5OjV7ka4OzsyVANJPj0EaPJ4z4fe', // Replace with your actual client_secret
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $token = $data['access_token'];

            // Cache the token for its lifetime minus a small buffer
            Cache::put('openremote_token', $token, $data['expires_in'] - 10);
        }

        return $token;
    }
}
