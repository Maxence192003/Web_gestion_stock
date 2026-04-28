<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ProduitStockInput;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class ProduitStockUpdateProcessor implements ProcessorInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Produit
    {
        if (!$data instanceof ProduitStockInput || $data->quantiteStock === null) {
            throw new BadRequestHttpException('La quantite de stock est requise.');
        }

        $produitId = $uriVariables['id'] ?? null;
        if (!is_numeric($produitId)) {
            throw new BadRequestHttpException('Produit introuvable.');
        }

        $produit = $this->entityManager->getRepository(Produit::class)->find((int) $produitId);
        if (!$produit instanceof Produit) {
            throw new EntityNotFoundException('Produit introuvable.');
        }

        $produit->setQuantiteStock($data->quantiteStock);
        $this->entityManager->persist($produit);
        $this->entityManager->flush();

        return $produit;
    }
}