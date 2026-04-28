<?php

namespace App\Controller;

use App\Repository\PerteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pertes')]
final class LossController extends AbstractController
{
    #[Route('', name: 'app_loss_index', methods: ['GET'])]
    public function index(PerteRepository $perteRepository): Response
    {
        return $this->render('loss/index.html.twig', [
            'pertes' => $perteRepository->findBy([], ['datePerte' => 'DESC', 'id' => 'DESC']),
        ]);
    }
}