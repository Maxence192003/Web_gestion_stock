<?php

namespace App\Repository;

use App\Entity\Perte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Perte>
 */
class PerteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Perte::class);
    }

    /**
     * @return array<int, array{nom: string, quantitePerdue: int}>
     */
    public function findTopLossProductsForPeriod(\DateTimeImmutable $start, \DateTimeImmutable $end, int $limit = 5): array
    {
        $rows = $this->createQueryBuilder('p')
            ->select('produit.nom AS nom, SUM(p.quantite) AS quantitePerdue')
            ->join('p.produit', 'produit')
            ->andWhere('p.datePerte >= :start')
            ->andWhere('p.datePerte < :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('produit.id, produit.nom')
            ->orderBy('quantitePerdue', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(static fn (array $row): array => [
            'nom' => (string) $row['nom'],
            'quantitePerdue' => (int) $row['quantitePerdue'],
        ], $rows);
    }

    /**
     * @return list<string>
     */
    public function findAvailableMonths(): array
    {
        return array_values($this->getEntityManager()->getConnection()->fetchFirstColumn(
            "SELECT DISTINCT TO_CHAR(date_perte, 'YYYY-MM') AS month_key
             FROM perte
             ORDER BY month_key DESC"
        ));
    }
}