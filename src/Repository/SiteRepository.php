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

        public function delete($value){
            $qb = $this->createQueryBuilder('s');
              $result = $qb->delete()
                ->where('s.id = :val')
                ->setParameter('val', $value)
                ->getQuery()
                ->execute();
            return $result >0;
        }

        public function updateActif($id){
            $qb = $this->createQueryBuilder('s')
                ->update()
                ->set('s.actif', 0)
                ->where('s.id = :id')
                ->setParameter('id', $id);
            $query = $qb->getQuery();
            return $query->getResult();
        }

        public function activate($id){
            $qb = $this->createQueryBuilder('s')
                ->update()
                ->set('s.actif', 1)
                ->where('s.id = :id')
                ->setParameter('id', $id);
            $query = $qb->getQuery();
            return $query->getResult();
        }



}
