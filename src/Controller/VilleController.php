<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ville', name: 'ville_')]
final class VilleController extends AbstractController
{
    #[Route('/list', name: 'list')]
    public function index(VilleRepository $villeRepository): Response
    {
        $villes = $villeRepository->findAll();


        return $this->render('ville/index.html.twig', [
            'villes' => $villes,
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ville = new Ville();
        $form = $this->createForm(VilleType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $form = $form->getData();
            $entityManager->persist($form);
            $entityManager->flush();
            return $this->redirectToRoute('ville_list');
        }

        return $this->render('ville/add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/update/{id}', name: 'update')]
    public function update(VilleRepository $villeRepository, Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $ville = $villeRepository->find($id);
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($form->getData());
            $entityManager->flush();
            return $this->redirectToRoute('ville_list');
        }
        return $this->render('ville/update.html.twig', [
            'form' => $form,
        ]);
    }


}
