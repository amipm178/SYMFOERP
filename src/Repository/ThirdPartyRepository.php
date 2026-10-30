<?php

namespace App\Repository;

use App\Entity\ThirdParty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ThirdParty>
 */
final class ThirdPartyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThirdParty::class);
    }

    /**
     * @return list<ThirdParty>
     */
    public function findRecent(int $limit = 20): array
    {
        $limit = max(1, min($limit, 100));

        return $this->createQueryBuilder('thirdParty')
            ->orderBy('thirdParty.createdAt', 'DESC')
            ->addOrderBy('thirdParty.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
