<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return Product[]
     */
    public function search(?string $shade = null, bool $inAlert = false): array
    {
        $qb = $this->createQueryBuilder('p')->orderBy('p.id');

        if ($shade) {
            $qb->andWhere('p.shade = :shade')->setParameter('shade', $shade);
        }

        if ($inAlert) {
            $qb->andWhere('p.stock < p.alertThreshold');
        }

        return $qb->getQuery()->getResult();
    }
}
