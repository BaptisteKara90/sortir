<?php

namespace App\Repository;

use App\Entity\Lieu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lieu>
 */
class LieuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lieu::class);
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

    /**
     * @return Lieu[] Returns an array of Site objects
     */
    public function findByNameFilter($value): array
    {
        $qb = $this->createQueryBuilder('l')
            ->where('l.nom LIKE :val')
            ->setParameter('val', '%' . $value .'%');
        $query =$qb->getQuery();
        return $query->getResult();
    }
}
