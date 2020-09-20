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
        return $this->createQueryBuilder(self::ALIAS)
            ->andWhere(self::ALIAS.'.reference = :val')
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
            ->andWhere(self::ALIAS.'.is_hazardous = :is_hazardous')
            ->setParameter('is_hazardous', $isHazardous);
    }

    public function getFastestNearEarthObject(QueryBuilder $qb = null)
    {
        $query = $this->getOrCreateQueryBuilder($qb);

        $maxSpeed = $query->select('MAX('.self::ALIAS.'.speed) as max_speed')
            ->getQuery()
            ->getSingleScalarResult();

        $query->select(self::ALIAS)
            ->andWhere(self::ALIAS.'.speed='.$maxSpeed)
            ->setMaxResults(1);

        return $query->getQuery()->execute();
    }

    public function getMonthWithMostNearEarthObjects(QueryBuilder $qb = null)
    {
        $query = $this->getOrCreateQueryBuilder($qb);

        //TODO: rewrite raw SQL query to ORM query
        //SELECT MONTH(date), COUNT(id) FROM near_earth_object GROUP BY MONTH(date) ORDER BY COUNT(id) DESC LIMIT 1
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null)
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
