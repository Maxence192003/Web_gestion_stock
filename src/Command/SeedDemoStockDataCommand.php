<?php

namespace App\Command;

use App\Entity\Perte;
use App\Entity\Produit;
use App\Entity\Vente;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-demo-stock-data',
    description: 'Create reusable demo products, ventes and pertes across multiple months.',
)]
final class SeedDemoStockDataCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $month0 = new \DateTimeImmutable('first day of this month 10:00:00');
        $monthMinus1 = $month0->modify('-1 month');
        $monthMinus2 = $month0->modify('-2 months');

        $productsData = [
            [
                'nom' => 'Demo Coca Cola 33cl',
                'typeProduit' => Produit::TYPE_BOISSON,
                'prixAchat' => '0.75',
                'prixVente' => '2.00',
                'initialStock' => 120,
                'ventes' => [
                    ['quantite' => 12, 'date' => $monthMinus2->modify('+3 days')],
                    ['quantite' => 18, 'date' => $monthMinus1->modify('+5 days')],
                    ['quantite' => 20, 'date' => $month0->modify('+2 days')],
                ],
                'pertes' => [
                    ['quantite' => 2, 'raison' => 'Perte', 'date' => $monthMinus2->modify('+8 days')],
                    ['quantite' => 1, 'raison' => 'Perte', 'date' => $monthMinus1->modify('+9 days')],
                    ['quantite' => 3, 'raison' => 'Perte', 'date' => $month0->modify('+4 days')],
                ],
            ],
            [
                'nom' => 'Demo Chips Barbecue',
                'typeProduit' => Produit::TYPE_NOURRITURE,
                'prixAchat' => '0.95',
                'prixVente' => '2.50',
                'initialStock' => 90,
                'ventes' => [
                    ['quantite' => 9, 'date' => $monthMinus2->modify('+6 days')],
                    ['quantite' => 14, 'date' => $monthMinus1->modify('+12 days')],
                    ['quantite' => 17, 'date' => $month0->modify('+6 days')],
                ],
                'pertes' => [
                    ['quantite' => 1, 'raison' => 'Perte', 'date' => $monthMinus1->modify('+18 days')],
                    ['quantite' => 2, 'raison' => 'Perte', 'date' => $month0->modify('+10 days')],
                ],
            ],
            [
                'nom' => 'Demo Eau 50cl',
                'typeProduit' => Produit::TYPE_BOISSON,
                'prixAchat' => '0.30',
                'prixVente' => '1.00',
                'initialStock' => 150,
                'ventes' => [
                    ['quantite' => 20, 'date' => $monthMinus2->modify('+10 days')],
                    ['quantite' => 25, 'date' => $monthMinus1->modify('+14 days')],
                    ['quantite' => 28, 'date' => $month0->modify('+8 days')],
                ],
                'pertes' => [
                    ['quantite' => 2, 'raison' => 'Perte', 'date' => $monthMinus2->modify('+16 days')],
                    ['quantite' => 3, 'raison' => 'Perte', 'date' => $month0->modify('+12 days')],
                ],
            ],
            [
                'nom' => 'Demo Sandwich Poulet',
                'typeProduit' => Produit::TYPE_NOURRITURE,
                'prixAchat' => '1.80',
                'prixVente' => '4.50',
                'initialStock' => 70,
                'ventes' => [
                    ['quantite' => 6, 'date' => $monthMinus2->modify('+11 days')],
                    ['quantite' => 11, 'date' => $monthMinus1->modify('+16 days')],
                    ['quantite' => 15, 'date' => $month0->modify('+9 days')],
                ],
                'pertes' => [
                    ['quantite' => 2, 'raison' => 'Perte', 'date' => $monthMinus1->modify('+20 days')],
                    ['quantite' => 4, 'raison' => 'Perte', 'date' => $month0->modify('+14 days')],
                ],
            ],
        ];

        foreach ($productsData as $productData) {
            $produit = $this->entityManager->getRepository(Produit::class)->findOneBy([
                'nom' => $productData['nom'],
            ]);

            if (!$produit instanceof Produit) {
                $produit = new Produit();
                $produit->setNom($productData['nom']);
                $this->entityManager->persist($produit);
            }

            foreach ($produit->getVentes()->toArray() as $vente) {
                $this->entityManager->remove($vente);
            }

            foreach ($produit->getPertes()->toArray() as $perte) {
                $this->entityManager->remove($perte);
            }

            $produit
                ->setTypeProduit($productData['typeProduit'])
                ->setPrixAchat($productData['prixAchat'])
                ->setPrixVente($productData['prixVente']);

            $stockFinal = $productData['initialStock'];

            foreach ($productData['ventes'] as $venteData) {
                $vente = (new Vente())
                    ->setProduit($produit)
                    ->setQuantite($venteData['quantite'])
                    ->setDateVente($venteData['date']);

                $this->entityManager->persist($vente);
                $stockFinal -= $venteData['quantite'];
            }

            foreach ($productData['pertes'] as $perteData) {
                $perte = (new Perte())
                    ->setProduit($produit)
                    ->setQuantite($perteData['quantite'])
                    ->setRaison($perteData['raison'])
                    ->setDatePerte($perteData['date']);

                $this->entityManager->persist($perte);
                $stockFinal -= $perteData['quantite'];
            }

            $produit->setQuantiteStock(max(0, $stockFinal));
        }

        $this->entityManager->flush();

        $io->success('Donnees de demonstration creees pour plusieurs mois.');

        return Command::SUCCESS;
    }
}