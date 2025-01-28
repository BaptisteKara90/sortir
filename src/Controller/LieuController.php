<?php

namespace App\Controller;


use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lieu', name: 'lieu_')]
final class LieuController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(LieuRepository $repository): Response
    {
        $lieux = $repository->findAll();

        return $this->render('lieu/list.html.twig', [
            'lieux' => $lieux,
        ]);
    }

    #[Route('/update/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(EntityManagerInterface $entityManager, LieuRepository $repository, int $id, Request $request): Response
    {
        $lieu = $repository->find($id);
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            $this->addFlash('success', "Le lieu {$lieu->getNom()} a été mis à jour avec succès !");
            return $this->redirectToRoute('lieu_list');
        }

        return $this->render('lieu/update.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function delete(Lieu $lieu, SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $sorties = $sortieRepository->findBylieu($lieu);

        if ($sorties) {

            $lieu->setActif(false);
            $entityManager->persist($lieu);

            $this->addFlash('success', "Le lieu {$lieu->getNom()} a été désactivé !");

        } else {

            foreach ($sorties as $sortie) {

                $etat = $etatRepository->findOneByLibelle('Annulée');
                $raison = 'Le lieu a été supprimé.';
                $sortie->setEtat($etat);
                $sortie->setRaison($raison);
                $sortie->setActif(false);
                $entityManager->persist($sortie);
            }

            $lieu->setActif(false);
            $entityManager->persist($lieu);
            $this->addFlash('success', "Le lieu {$lieu->getNom()} ainsi que toutes les sorties associées ont été désactivées !");
        }

        $entityManager->flush();

        return $this->redirectToRoute('lieu_list');
    }

    #[Route('/activate/{id}', name: 'activate', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function activate(Lieu $lieu, EntityManagerInterface $entityManager): Response
    {
        $lieu->setActif(true);
        $entityManager->persist($lieu);
        $entityManager->flush();

        $this->addFlash("success", "Le lieu {$lieu->getNom()} a été réactivé !");
        return $this->redirectToRoute('lieu_list');
    }
}
