<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produits')]
final class ProductController extends AbstractController
{
    #[Route('/api', name: 'app_product_api_redirect', methods: ['GET'])]
    public function redirectToApiDocs(): RedirectResponse
    {
        return $this->redirect('/api/docs');
    }

    #[Route('', name: 'app_product_index', methods: ['GET'])]
    public function index(Request $request, ProduitRepository $produitRepository): Response
    {
        $selectedType = (string) $request->query->get('type', '');
        $criteria = [];

        if (in_array($selectedType, Produit::AVAILABLE_TYPES, true)) {
            $criteria['typeProduit'] = $selectedType;
        } else {
            $selectedType = '';
        }

        return $this->render('product/index.html.twig', [
            'produits' => $produitRepository->findBy($criteria, ['nom' => 'ASC']),
            'selectedType' => $selectedType,
            'availableTypes' => [
                Produit::TYPE_BOISSON => 'Boisson',
                Produit::TYPE_NOURRITURE => 'Nourriture',
                Produit::TYPE_AUTRE => 'Autre',
            ],
        ]);
    }

    #[Route('/nouveau', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $produit->setTypeProduit(Produit::TYPE_AUTRE);
        $produit->setQuantiteStock(0);
        $produit->setPrixAchat('0.00');
        $produit->setPrixVente('0.00');

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit cree avec succes.');

            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/form.html.twig', [
            'title' => 'Nouveau produit',
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Produit $produit, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Produit mis a jour.');

            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/form.html.twig', [
            'title' => sprintf('Modifier %s', $produit->getNom()),
            'form' => $form,
        ]);
    }
}