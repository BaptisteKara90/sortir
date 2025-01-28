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

        return $this->render('ville/list.html.twig', [
            'villes' => $villes,
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($ville);
            try {
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }

            $this->addFlash('success', "l'ajout de la ville {$ville->getNom()} a correctement été effectué !");
            return $this->redirectToRoute('ville_list');
        }

        return $this->render('ville/add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/update/{id}', name: 'update', requirements: ['id' => '\d+'])]
    public function update(Ville $ville, Request $request, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($ville);
            try {
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }

            $this->addFlash("success", "La ville {$ville->getNom()} a correctement été modifiée !");
            return $this->redirectToRoute('ville_list');
        }
        return $this->render('ville/update.html.twig', [
            'form' => $form,
        ]);
    }
}
