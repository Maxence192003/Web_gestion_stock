<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Produit;
use App\Entity\Vente;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class VenteStateProcessor implements ProcessorInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Vente
    {
        if (!$data instanceof Vente) {
            throw new BadRequestHttpException('Donnees de vente invalides.');
        }

        $produit = $data->getProduit();
        if (!$produit instanceof Produit) {
            throw new BadRequestHttpException('Le produit est requis.');
        }

        $quantite = $data->getQuantite();
        if ($quantite === null || $quantite <= 0) {
            throw new BadRequestHttpException('La quantite doit etre strictement positive.');
        }

        $stockActuel = $produit->getQuantiteStock() ?? 0;
        if ($quantite > $stockActuel) {
            throw new BadRequestHttpException('Stock insuffisant pour enregistrer cette vente.');
        }

        if ($data->getDateVente() === null) {
            $data->setDateVente(new \DateTimeImmutable());
        }

        $produit->setQuantiteStock($stockActuel - $quantite);

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}