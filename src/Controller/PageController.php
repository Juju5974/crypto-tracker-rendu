<?php

namespace App\Controller;

use App\Service\CallApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CurrencyRepository;
use App\Repository\ValuationRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\FormController;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class PageController extends AbstractController
{   
    #[Route('/', name: 'index')]
    public function index(CallApiService $callApiService, CurrencyRepository $currencyRepo): Response
    {
        $cryptoApiKey = $this->getParameter('CRYPTO_API_KEY');
        $apiResponse = json_decode($callApiService->getCryptoData($cryptoApiKey), true);
        $total = $currencyRepo->find(1)->getAmount();
        $investedCurrencies = $currencyRepo->findAllGreaterThanZero();
        $changes = [];
        for ($i = 0; $i < count($investedCurrencies); $i++)
        {
            $key = array_search($investedCurrencies[$i]->getIdApi(), array_column($apiResponse['data'], 'id'));
            $changes[$i] = $apiResponse['data'][$key]['quote']['EUR']['percent_change_24h'];           
        }

        return $this->render('page/index.html.twig', [
            'investedCurrencies' => $investedCurrencies,
            'changes' => $changes,
            'total' => $total
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(
        CallApiService $callApiService,
        CurrencyRepository $currencyRepo,
        Request $request,
        EntityManagerInterface $em,
        FormController $form): Response
    {
        $cryptoApiKey = $this->getParameter('CRYPTO_API_KEY');
        $apiResponse = json_decode($callApiService->getCryptoData($cryptoApiKey), true);
        $currencies = $currencyRepo->findAll();
        $addForm = $form->getForm($currencies, $request);
        $form->flushForm($request, $addForm, $currencyRepo, $em, $apiResponse);   
        return $this->render('page/add.html.twig', [
            'apiReponse' => $apiResponse,
            'currencies' => $currencies,
            'currencyForm' => $addForm->createView()
        ]);
    }

    #[Route('/remove', name: 'remove')]
    public function remove(
        CallApiService $callApiService,
        CurrencyRepository $currencyRepo, 
        Request $request, 
        EntityManagerInterface $em,
        FormController $form): Response
    {
        $cryptoApiKey = $this->getParameter('CRYPTO_API_KEY');
        $apiResponse = json_decode($callApiService->getCryptoData($cryptoApiKey), true);
        $currencies = $currencyRepo->findAll();
        $removeForm = $form->getForm($currencies, $request);
        $form->flushForm($request, $removeForm, $currencyRepo, $em, $apiResponse);
        return $this->render('page/remove.html.twig', [
            'currencies' => $currencies,
            'currencyForm' => $removeForm->createView()
        ]);
    }

    #[Route('/chart', name: 'chart')]
    public function chart(
        ValuationRepository $valuationRepo, 
        ChartBuilderInterface $chartBuilder): Response
    {
        $valuations = $valuationRepo->findAll();
        $labels = [];
        $data = [];

        foreach ($valuations as $valuation) {
            $labels[] = $valuation->getDate()->format('d/m');
            $data[] = $valuation->getDelta();
        }
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' =>$labels,
            'datasets' => [
                [
                    'pointRadius' => 0,
                    'tension' => 0.5,
                    'backgroundColor' => 'rgb(255, 255, 255)',
                    'borderColor' => 'rgb(31, 195, 108)',
                    'data' => $data,
                ],
            ],
        ]);

        $chart->setOptions(['plugins' => [
            'legend' => [
                'display' => false
            ]
        ]]);
        return $this->render('page/chart.html.twig', [
            'chart' => $chart
        ]);
    }
}