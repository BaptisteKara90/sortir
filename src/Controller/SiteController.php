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

            if(!$filterValue){
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
            $site->setNom($form->get("nom")->getData());
            $site->setActif(true);

            $entityManager->persist($site);

            $entityManager->flush();
            return $this->redirectToRoute('site_list');
        }

        return $this->render('site/add.html.twig',[
            'form' => $form,
            ]);
    }

    #[Route('/update/{id}', name: 'update', methods: ['GET', 'POST'])]
    public function update(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        $site = $entityManager->getRepository(Site::class)->find($id);
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($form->getData());
            $entityManager->flush();
           return $this->redirectToRoute('site_list');
        }
        return $this->render('site/update.html.twig',[
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(SortieRepository $sortieRepository, SiteRepository $siteRepository, Request $request, int $id): Response
    {
        $site = $siteRepository->find($id);
        $sortie = $sortieRepository->findBySite($site);

        if($sortie){
            $siteRepository->updateActif($site->getId());
            return $this->redirectToRoute('site_list');
        }
        $result= $siteRepository->delete($id);
       if(!$result){
           //TODO add flash !
           return $this->redirectToRoute('site_list');
       }
       return $this->redirectToRoute('site_list');

    }

    #[Route('/activate/{id}', name: 'activate', methods: ['GET', 'POST'])]
    public function activate(SortieRepository $sortieRepository, SiteRepository $siteRepository, Request $request, int $id): Response
    {
         $siteRepository->activate($id);
         return $this->redirectToRoute('site_list');
    }
}
