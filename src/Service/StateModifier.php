<?php

namespace App\Service;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class StateModifier
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

    public function stateModifier(){
        $date = new \DateTime('now');
        $date->modify('+1 hour');
        $sortieRepository = $this->sortieRepository;
        $etatRepository = $this->etatRepository;
        $etatCloturee = $etatRepository->findOneByLibelle("Clôturée");
        $etatEnCours = $etatRepository->findOneByLibelle("Activité en cours");
        $etatPassee= $etatRepository->findOneByLibelle("Passée");
        $sorties = $sortieRepository->findAll();
        foreach ($sorties as $sortie){
            $duree = new \DateInterval("PT{$sortie->getDuree()}M");
            $dateFin = (clone $sortie->getDebut())->add($duree);
            if($sortie->getEtat()->getLibelle() == 'Ouverte' && $sortie->getDateLimitInscription() <= $date){
                $sortie->setEtat($etatCloturee);
            }if ($sortie->getEtat()->getLibelle() == 'Clôturée' && $sortie->getDebut() < $date && $dateFin > $date){
                $sortie->setEtat($etatEnCours);
            }if ($sortie->getEtat()->getLibelle() == "Activité en cours" && $dateFin < $date){
                $sortie->setEtat($etatPassee);
            }
            $this->entityManager->persist($sortie);
        }
        $this->entityManager->flush();
    }
}



