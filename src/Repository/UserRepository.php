<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findOneById($id): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByEmail($email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findByFilter(object $filter) {

        $q = $this->createQueryBuilder('u');
        if ($filter->nom) {
            $q->andWhere('u.nom LIKE :nom')->setParameter('nom', '%'.$filter->nom.'%');
        }
        if ($filter->prenom) {
            $q->andWhere('u.prenom LIKE :prenom')->setParameter('prenom', '%'.$filter->prenom.'%');
        }
        if ($filter->email) {
            $q->andWhere('u.email LIKE :email')->setParameter('email', '%'.$filter->email.'%');
        }
        if ($filter->site) {
            $q->andWhere('u.site = :site')->setParameter('site', $filter->site);
        }
        if ($filter->actif) {
            if($filter->actif === "actif"){
                $q->andWhere('u.actif = :actif')->setParameter('actif', true);
            } else {
                $q->andWhere('u.actif = :actif')->setParameter('actif', false);
            }

        }
        $query = $q->getQuery();
        return $query->getResult();
    }
}
