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


    public function updateActif(int $id){
        $qb = $this->createQueryBuilder('s');
        $qb->update()
            ->set('s.actif',':actif')
            ->where('s.id = :id')
            ->setParameter('id',$id)
            ->setParameter('actif',0);
        $query = $qb->getQuery()->execute();
        if($query ===0){
            return false;
        }

        return true;
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
