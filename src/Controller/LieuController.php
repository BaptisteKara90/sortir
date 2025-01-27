<?php

namespace App\Controller;


use App\Entity\Ville;
use App\Form\LieuType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;


use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/lieu', name:'lieu_')]
final class LieuController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(LieuRepository $repository): Response
    {
        $lieux=$repository->findAll();

        return $this->render('lieu/index.html.twig', [
            'lieux' => $lieux,
            ]);
    }

    #[Route('/update/{id}', name: 'update', methods: ['GET', 'POST'])]
    public function update(EntityManagerInterface $entityManager, LieuRepository $repository, int $id, Request $request): Response
    {
        $lieu=$repository->find($id);
        $form=$this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();
            return $this->redirectToRoute('lieu_list');
        }
        return $this->render('lieu/update.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(LieuRepository $lieuRepository, SortieRepository $sortieRepository , EtatRepository $etatRepository, Request $request, int $id): Response
    {
        $lieu=$lieuRepository->find($id);
        $sorties = $sortieRepository->findBylieu($lieu);


        if(!$sorties){
            $lieu->remove($lieu);
            return $this->redirectToRoute('lieu_list');
        }
        else{
            foreach ($sorties as $sortie){
                $etat = $etatRepository->findOneByLibelle('Annulée');
                $raison = 'Le lieu a été supprimé.';
                $sortieRepository->cancel($sortie->getId(), $etat->getId(), $raison);

            }
            $lieuRepository->updateActif($lieu->getId());
        }
        //TODO add Flash
        return $this->redirectToRoute('lieu_list');
    }
    #[Route('/activate/{id}', name: 'activate', methods: ['GET', 'POST'])]
    public function activate(LieuRepository $lieuRepository, SortieRepository $sortieRepository , EtatRepository $etatRepository, Request $request, int $id): Response
    {
        $lieu=$lieuRepository->activate($id);
        return $this->redirectToRoute('lieu_list');
    }



}
