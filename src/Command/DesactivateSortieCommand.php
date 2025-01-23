<?php

namespace App\Command;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:desactivate-sortie',
    description: 'Désactive les sorties au terminées depuis plus de 30 jours',
)]
class DesactivateSortieCommand extends Command
{

    private SortieRepository $sortieRepository;
    private EtatRepository $etatRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(SortieRepository $sortieRepository,EtatRepository $etatRepository, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
        $this->entityManager = $em;

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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

        $output->writeln('Les sorties terminées depuis plus de 30 jours on été désactivées');

        return Command::SUCCESS;
    }
}
