<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findBySite($site){
       $qb = $this->createQueryBuilder('s');
       $qb->where('s.site = :site')->setParameter('site', $site);
        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function findByOption(array $data, $user){
        $qb = $this->createQueryBuilder('s');
        if($data['site']){
            $qb->where('s.site = :site')->setParameter('site', $data['site']->getId());
        }
        if (!empty($data['content'])) {
            $qb->andWhere('s.nom LIKE :content')
                ->setParameter('content', '%' . $data['content'] . '%');
        }
        if (!empty($data['dateDebut'])) {
            $qb->andWhere('s.debut >= :debut')
                ->setParameter('debut', $data['dateDebut']);
        }
        if (!empty($data['dateFin'])) {
            $qb->andWhere('s.debut <= :fin')
                ->setParameter('fin', $data['dateFin']);
        }
        if ($data['organisateur']) {
            $qb->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $user->getId());
        }
        //TODO participant
//        if($data['inscrit']) {
//            $qb->andWhere('s.inscrit = :inscrit')
//                ->setParameter('inscrit', $data['inscrit']);
//        }
        if ($data['sortiePassee']) {
            $qb->andWhere('s.etat = :etat')
                ->setParameter('etat', 5);
        }
        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function cancel(int $id, int $idEtat, string $raison): bool{
        var_dump($id);
        $qb = $this->createQueryBuilder('s');
        $qb->update()
            ->set('s.etat', ':etat')
            ->set('s.raison', ':raison')
            ->where('s.id = :id')
            ->setParameter('etat', $idEtat)
            ->setParameter('id', $id)
            ->setParameter('raison', $raison);
        $query = $qb->getQuery()->execute();
        if($query === 0){
            return false;
        }
        return true;
    }
}
