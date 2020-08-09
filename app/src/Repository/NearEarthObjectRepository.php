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
     * @return QueryBuilder
     */
    public function findAllQueryBuilder()
    {
        return $this->createQueryBuilder('n');
    }

    // /**
    //  * @return NearEarthObject[] Returns an array of NearEarthObject objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
