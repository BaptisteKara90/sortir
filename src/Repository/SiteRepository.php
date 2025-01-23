<?php

namespace App\Repository;

use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Site>
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

        /**
         * @return Site[] Returns an array of Site objects
         */
        public function findByNameFilter($value): array
        {
            $qb = $this->createQueryBuilder('s')
                ->where('s.nom LIKE :val')
                ->setParameter('val', '%' . $value .'%');
            $query =$qb->getQuery();
            return $query->getResult();
        }

    //    public function findOneBySomeField($value): ?Site
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
