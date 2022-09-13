<?php

namespace App\Command;

use App\Service\CallApiService;
use Symfony\Component\Console\Command\Command;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ValuationController;

class ValuationCommand extends Command
{
    
    
    public function __construct()
    {

        parent::__construct();
    }
    
    public function saveValuation(
        CallApiService $callApiService,
        CurrencyRepository $currencyRepo,
        EntityManagerInterface $em,
        ValuationController $valuationController)
    {
        $valuationController->saveValuation($callApiService, $currencyRepo, $em);
    }
}