<?php

namespace App\Controller;

use App\Service\CallApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{   
    #[Route('/', name: 'index')]
    public function index(CallApiService $callApiService): Response
    {
        $cryptoApiKey = $this->getParameter('CRYPTO_API_KEY');

        $decode = json_decode($callApiService->getCryptoData($cryptoApiKey));
        dump($decode);
        return $this->render('page/index.html.twig', [
            'data' => $decode
        ]);
    }
    #[Route('/add', name: 'add')]
    public function add(): Response
    {
        return $this->render('page/add.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
    #[Route('/remove', name: 'remove')]
    public function remove(): Response
    {
        return $this->render('page/remove.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
    #[Route('/chart', name: 'chart')]
    public function chart(): Response
    {
        return $this->render('page/chart.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
}
