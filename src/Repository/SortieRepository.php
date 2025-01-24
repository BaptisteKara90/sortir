<?php

namespace App\Repository;

use App\Entity\Etat;
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

    public function findBySite($site, $user = null)
    {
        $qb = $this->createQueryBuilder('s');

        // Conditions de base
        $qb->where('s.site = :site')
            ->setParameter('site', $site)
            ->andWhere('s.active = true')
            ->join('s.etat', 'etat');

        // Si l'utilisateur n'est pas admin
        if ($user !== null && !in_array("ROLE_ADMIN", $user->getRoles())) {
                $qb->andWhere(
                    $qb->expr()->orX(
                        's.organisateur = :user',
                        'etat.libelle != :libelle'
                    )
                )
                    ->setParameter('user', $user)
                    ->setParameter('libelle', 'Créée');
        }


        // Exécution de la requête
        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function findByOption(array $data, $user){
        $qb = $this->createQueryBuilder('s');
        $qb->join('s.etat', 'etat');
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
        if($data['inscrit']) {
            $qb->andWhere(':inscrit MEMBER OF s.participants')
                ->setParameter('inscrit', $user);
        }
        if ($data['sortiePassee']) {
                $qb->andWhere('etat.libelle = :libelle')
                ->setParameter('libelle', 'Passée');
        }

        // Si l'utilisateur n'est pas admin
        if (!in_array("ROLE_ADMIN", $user->getRoles())) {
            $qb->andWhere(
                $qb->expr()->orX(
                    's.organisateur = :user',
                    'etat.libelle != :libelle'
                )
            )
                ->setParameter('user', $user)
                ->setParameter('libelle', 'Créée');
        }

        $qb->andWhere('s.active = true');
        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function findByEtat(Etat $etat){


        $qb = $this->createQueryBuilder('s');
        $qb->where('s.etat = :etat')->setParameter('etat', $etat);
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
