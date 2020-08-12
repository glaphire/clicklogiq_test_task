<?php

namespace App\Repository;

use App\Entity\NearEarthObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NearEarthObject|null find($id, $lockMode = null, $lockVersion = null)
 * @method NearEarthObject|null findOneBy(array $criteria, array $orderBy = null)
 * @method NearEarthObject[]    findAll()
 * @method NearEarthObject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NearEarthObjectRepository extends ServiceEntityRepository
{
    private const ALIAS = 'n';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NearEarthObject::class);
    }

    public function findOneReference($value): ?NearEarthObject
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.reference = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param bool $isHazardous
     *
     * @return QueryBuilder
     */
    public function isHazardousQueryBuilder($isHazardous = true, QueryBuilder $qb = null)
    {
        return $this->getOrCreateQueryBuilder($qb)
            ->where('n.is_hazardous = :is_hazardous')
            ->setParameter('is_hazardous', $isHazardous);
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null)
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
