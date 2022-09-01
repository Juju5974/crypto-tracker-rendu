<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallApiService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    
    public function getCryptoData($cryptoApiKey)
    {
        
        $response = $this->client->request(
            'GET',
            'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest', [
                'headers' => [
                    'Content-Type' => 'text/plain',
                    'X-CMC_PRO_API_KEY' => $cryptoApiKey
                ], 
                'query' => [
                    'convert' => 'EUR',
                    'limit' => 30
                ],
            ]
        );
        return $response->getContent();
    }
}