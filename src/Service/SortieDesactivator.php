<?php

namespace App\Service;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class SortieDesactivator
{

    private SortieRepository $sortieRepository;
    private EtatRepository $etatRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(SortieRepository $sortieRepository,EtatRepository $etatRepository, EntityManagerInterface $em)
    {
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
        $this->entityManager = $em;

    }

    public function desactivateOldSorties(): void
    {
        $etatPassee = $this->etatRepository->findOneByLibelle("passee");
        $sorties = $this->sortieRepository->findByEtat($etatPassee);
        $now = new \DateTime();

        foreach ($sorties as $sortie) {

            $dateFin = $sortie->getDebut()->modify('+' . $sortie->getDuree() . ' minutes');
            $dateFinOneMonthAfter = $dateFin->modify('+30 days');

            if ($dateFinOneMonthAfter->getTimestamp() < $now->getTimestamp()) {

                $sortie->setActive(false);
                $this->entityManager->persist($sortie);
            }
        }

        $this->entityManager->flush();
    }
}