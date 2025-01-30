<?php

namespace App\Repository;

use App\Entity\Ville;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ville>
 */
class VilleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ville::class);
    }

    /**
     * @param int $lieuId
     * @return mixed
     */
    public function findVillesByLieu(int $lieuId){
        $qb= $this->createQueryBuilder('v');

            $qb->where('v.lieu = :lieu')
                ->setParameter('lieu',$lieuId);

                $query = $qb->getQuery();
                return $query->getResult();

    }

    /**
     * @param $value
     * @return Ville[]
     */
    public function findByNameFilter($value): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.nom LIKE :val')
            ->setParameter('val', '%' . $value .'%');
        $query =$qb->getQuery();
        return $query->getResult();
    }
}
