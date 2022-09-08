<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\HttpFoundation\Request;

class FormController extends AbstractController
{   
    public function getForm($currencies, $request)
    {
        $routeName = $request->attributes->get('_route');
        $options = [];
        for ($i = 1; $i <= 30; $i++)
        {
            $options[$currencies[$i]->getName() . ' (' . $currencies[$i]->getSymbol() . ')'] = $currencies[$i]->getIdApi();
        }
        $formRequest = $this->createFormBuilder([])
            ->add('currency', ChoiceType::class, [
                'choices' => ['Sélectionner une crypto' => false] + $options,
            ])
            ->add('quantity', NumberType::class, [
                'attr' => ['placeholder' => 'Quantité'],
                'constraints' => new PositiveOrZero(['message' => 'Le montant doit être positif.'])
            ])
            ->add('amount', NumberType::class, [
                'attr' => ['placeholder' => 'Prix d\'achat'],
                'constraints' => new PositiveOrZero(['message' => 'Le montant doit être positif.'])
            ])
            ->add('submit', SubmitType::class, ['label' => 'VALIDER'])
            ->getForm();
        if ($routeName == 'remove')
        {
            $formRequest->remove('amount');
        }
        $formRequest->handleRequest($request);
        return $formRequest;
    }

    public function flushForm($request, $formRequest, $currencyRepo, $em, $apiData)
    {
        if ($formRequest->isSubmitted() && $formRequest->isValid()) 
        {
            $routeName = $request->attributes->get('_route');
            $data = $formRequest->getData();
            // $selectedCurrency like id_api
            $selectedCurrency = $data['currency'];
            $selectedQuantity = $data['quantity'];
            $euroConversion = $apiData->data[$selectedCurrency]->quote->EUR->price;
            $selectedAmount = $selectedQuantity * $euroConversion;
            $selectedCurrencyRepo = $currencyRepo->findBy(['idApi' => $selectedCurrency]);
            $currentName = $selectedCurrencyRepo[0]->getName();
            $currentAmount = $selectedCurrencyRepo[0]->getAmount();

            if ($routeName == 'add')
            {

            } else if ($routeName == 'remove')
            {
                $selectedAmount = - $selectedAmount;
            }
            $newAmount = $currentAmount + $selectedAmount;
            if ($newAmount >= 0)
            {
                $selectedCurrencyRepo[0]->setAmount($newAmount);
                $em->flush();
                $this->addFlash('success', 'La quantité de ' . $selectedQuantity . ' ' . $currentName . ' € a bien été pris en compte.');
            } else if ($newAmount < 0) 
            {
                $this->addFlash('fail', 'Le solde pour le ' . $currentName . ' ne peut pas être négatif.');
            } else 
            {
                $this->addFlash('error', 'Le serveur a rencontré une erreur.');
            }
            return $this->redirectToRoute('remove');
        }
    }
}