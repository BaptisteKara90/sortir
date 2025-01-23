<?php

namespace App\Controller;

use App\Form\SiteFilterType;
use App\Repository\SiteRepository;
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
}
