<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class CallApiService
{
    private $client;
    private $twig;

    public function __construct(HttpClientInterface $client, Environment $twig)
    {
        $this->client = $client;
        $this->twig = $twig;
    }
    
    public function getCryptoData($cryptoApiKey)
    {
        try
        {
            $response = $this->client->request(
                'GET',
                'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest/test', [
                    'headers' => [
                        'Content-Type' => 'text/plain',
                        'X-CMC_PRO_API_KEY' => $cryptoApiKey
                    ], 
                    'query' => [
                        'convert' => 'EUR',
                        'limit' => 35
                    ],
                ]
            );
            return $response->getContent();
            
        } 
        catch (\Exception $e) 
        {
            die('Le site rencontre un problème. Nous nous efforçons de le régler le plus rapidement possible. Veuillez nous excuser pour le désagrément.');
        }
    }
}