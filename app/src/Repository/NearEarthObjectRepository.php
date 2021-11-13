<?php

namespace App\Repository;

use App\Entity\NearEarthObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NearEarthObject|null find($id, $lockMode = null, $lockVersion = null)
 * @method NearEarthObject|null findOneBy(array $criteria, array $orderBy = null)
 * @method NearEarthObject[]    findAll()
 * @method NearEarthObject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NearEarthObjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NearEarthObject::class);
    }

    public function findOneReference($value): ?NearEarthObject
    {
        return $this->createQueryBuilder('neo')
            ->andWhere('neo.reference = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getFastestNearEarthObject(bool $isHazardous = false)
    {
        $query = $this->createQueryBuilder('neo');

        $maxSpeed = $query
            ->select('MAX(neo.speed) as max_speed')
            ->addCriteria(self::createIsHazardousCriteria($isHazardous))
            ->getQuery()
            ->getSingleScalarResult();

        $query->select('neo')
            ->andWhere('neo.speed='.$maxSpeed)
            ->setMaxResults(1);

        return $query->getQuery()->execute();
    }

    public static function createIsHazardousCriteria(bool $isHazardous = false): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('neo.is_hazardous', $isHazardous));
    }

    public function getMonthWithMostNearEarthObjects(bool $isHazardous)
    {
        $query = $this->createQueryBuilder('neo');

        $query->select('MONTHNAME(neo.date) AS best_month')
            ->addCriteria(self::createIsHazardousCriteria($isHazardous))
            ->groupBy('best_month')
            ->orderBy('COUNT(neo.id)')
            ->setMaxResults(1);
        ;

        return $query->getQuery()->getSingleScalarResult();
    }
}
