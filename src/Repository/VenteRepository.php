<?php

namespace App\Repository;

use App\Entity\Vente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vente>
 */
class VenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vente::class);
    }

    /**
     * @return array<int, array{nom: string, quantiteVendue: int}>
     */
    public function findTopSellingProductsForPeriod(\DateTimeImmutable $start, \DateTimeImmutable $end, int $limit = 5): array
    {
        $rows = $this->createQueryBuilder('v')
            ->select('p.nom AS nom, SUM(v.quantite) AS quantiteVendue')
            ->join('v.produit', 'p')
            ->andWhere('v.dateVente >= :start')
            ->andWhere('v.dateVente < :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('p.id, p.nom')
            ->orderBy('quantiteVendue', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(static fn (array $row): array => [
            'nom' => (string) $row['nom'],
            'quantiteVendue' => (int) $row['quantiteVendue'],
        ], $rows);
    }

    public function getMonthlyRevenue(\DateTimeImmutable $start, \DateTimeImmutable $end): float
    {
        $value = $this->createQueryBuilder('v')
            ->select('SUM(v.quantite * p.prixVente)')
            ->join('v.produit', 'p')
            ->andWhere('v.dateVente >= :start')
            ->andWhere('v.dateVente < :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($value ?? 0);
    }

    /**
     * @return list<string>
     */
    public function findAvailableMonths(): array
    {
        return array_values($this->getEntityManager()->getConnection()->fetchFirstColumn(
            "SELECT DISTINCT TO_CHAR(date_vente, 'YYYY-MM') AS month_key
             FROM vente
             ORDER BY month_key DESC"
        ));
    }
}