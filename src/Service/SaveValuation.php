<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Entity\Valuation;

class SaveValuation
{   
    private $getParams;

    public function __construct(ParameterBagInterface $getParams)
    {
        $this->getParams = $getParams;
    }
    
    public function save($callApiService, $currencyRepo, $em)
    {
        $cryptoApiKey = $this->getParams->get('CRYPTO_API_KEY');
        $apiResponse = json_decode($callApiService->getCryptoData($cryptoApiKey), true);
        $currencies = $currencyRepo->findAll();
        $totalRepo = 0;
        $newTotal = 0;
        for ($i = 2; $i <= 31; $i++)
        {
            $totalRepo += $currencies[$i]->getQuantity();
            $key = array_search($currencies[$i]->getIdApi(), array_column($apiResponse['data'], 'id'));
            $euroConversion = $apiResponse['data'][$key]['quote']['EUR']['price'];
            $newAmount = $currencies[$i]->getQuantity() * $euroConversion;
            $currencies[$i]->setAmount($newAmount);
            $newTotal += $newAmount;
        }
        $currencies[0]->setAmount($newTotal);
        $delta = $newTotal - $totalRepo;
        $valuation = new Valuation();
        $valuation->setDate(new \DateTime('now'));
        $valuation->setDelta($delta);
        $em->persist($valuation);
        $em->flush();
    }
}