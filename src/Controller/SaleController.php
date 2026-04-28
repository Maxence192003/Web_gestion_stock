<?php

namespace App\Controller;

use App\Repository\VenteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ventes')]
final class SaleController extends AbstractController
{
    #[Route('', name: 'app_sale_index', methods: ['GET'])]
    public function index(VenteRepository $venteRepository): Response
    {
        return $this->render('sale/index.html.twig', [
            'ventes' => $venteRepository->findBy([], ['dateVente' => 'DESC', 'id' => 'DESC']),
        ]);
    }
}