<?php

namespace App\Repository;

use App\Entity\ProductInStore;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductInStore|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductInStore|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductInStore[]    findAll()
 * @method ProductInStore[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductInStoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductInStore::class);
    }

    // /**
    //  * @return ProductInStore[] Returns an array of ProductInStore objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProductInStore
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
