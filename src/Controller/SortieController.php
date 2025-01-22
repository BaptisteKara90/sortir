<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\FilterType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/sortie', name: 'sortie_')]
final class SortieController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET', 'POST'])]
    public function index(SortieRepository $sortieRepository, Request $request, EntityManagerInterface $entityManager): Response{
        $user = $this->getUser();
        $formFilter = $this->createForm(FilterType::class);
        $formFilter->handleRequest($request);

        if($formFilter->isSubmitted() && $formFilter->isValid()){
            $data = $formFilter->getData();
            $sortieRepository->findByOption($data,$user);
            return $this->render('sortie/index.html.twig', [
                'sorties' => $sortieRepository->findByOption($data,$user),
                'formFilter' => $formFilter,
            ]);
        }

        $sorties = $sortieRepository->findBySite($this->getUser()->getSite());
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
            'formFilter' => $formFilter,
        ]);
    }
    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setOrganisateur($this->getUser());
            $entityManager->persist($sortie);
            $entityManager->flush();
            return $this->redirectToRoute('accueil');
        }
        return $this->render('sortie/add.html.twig', [
            'sortieForm' => $form,
        ]);
    }
    #[Route('/{id}', name: 'cancel', methods: ['GET'])]
    public function cancel(Request $request, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser();
        if($user->getId() !== $sortie->getOrganisateur()->getId()){
            return $this->redirectToRoute('accueil');
        }

        return $this->render('sortie/cancel.html.twig', []);
    }
}
