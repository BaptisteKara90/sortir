<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteFilterType;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/site', name: 'site_')]
final class SiteController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET', 'POST'])]
    public function index(SiteRepository $siteRepository, Request $request): Response
    {
        $filter = $this->createForm(SiteFilterType::class);
        $filter->handleRequest($request);

        if ($filter->isSubmitted() && $filter->isValid()) {
            $filterValue = $filter->get("nom")->getData();
            $sites = $siteRepository->findByNameFilter($filterValue);

            if (!$filterValue) {
                $sites = $siteRepository->findAll();
            }

            return $this->render('site/list.html.twig', [
                'sites' => $sites,
                'filterForm' => $filter,
            ]);
        } else {
            $sites = $siteRepository->findAll();
            return $this->render('site/list.html.twig', [
                'sites' => $sites,
                'filterForm' => $filter,
            ]);
        }
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(EntityManagerInterface $entityManager, Request $request): Response
    {
        $site = new Site();
        $form = $this->createForm(SiteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $site->setNom($form->get("nom")->getData());
                $site->setActif(true);
                $entityManager->persist($site);
                $entityManager->flush();
            } catch (\Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
                return $this->redirectToRoute('site_list');
            }

            $this->addFlash("success", "L'ajout du site a correctement été effectué !");
            return $this->redirectToRoute('site_list');
        }

        return $this->render('site/add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/update/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(Site $site, EntityManagerInterface $entityManager, Request $request): Response
    {
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $entityManager->persist($form->getData());
                $entityManager->flush();
            } catch (\Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
                return $this->redirectToRoute('site_list');
            }

            $this->addFlash("success", "Le site {$site->getNom()} a correctement été mis à jour !");
            return $this->redirectToRoute('site_list');
        }

        return $this->render('site/update.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function delete(Site $site, SortieRepository $sortieRepository, EntityManagerInterface $entityManager): Response
    {
        $sorties = $sortieRepository->findBySite($site);

        if ($sorties) {

            $site->setActif(false);
            $entityManager->persist($site);
            try {
                $entityManager->flush();
            } catch (\Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
            $this->addFlash("success", "Le site a été désactivé avec succès !");

        } else {
            $entityManager->remove($site);
            try {
                $entityManager->flush();
            } catch (\Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
            $this->addFlash("success", "Le site {$site->getNom()} a été supprimé avec succès !");
        }

        return $this->redirectToRoute('site_list');
    }

    #[Route('/activate/{id}', name: 'activate', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function activate(Site $site, EntityManagerInterface $entityManager): Response
    {
        $site->setActif(true);
        $entityManager->persist($site);
        try {
            $entityManager->flush();
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        $this->addFlash('success', "Le site {$site->getNom()} a été réactivé !");
        return $this->redirectToRoute('site_list');
    }
}
