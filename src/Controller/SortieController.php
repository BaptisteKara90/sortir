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
    #[Route('/', name: 'list', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository, Request $request, EntityManagerInterface $entityManager): Response{
        $formFilter = $this->createForm(FilterType::class);
        $formFilter->handleRequest($request);

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
}
