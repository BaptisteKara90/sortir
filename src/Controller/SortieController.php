<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\FilterType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
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
    #[Route('/cancel/{id}', name: 'cancel', methods: ['GET'])]
    public function cancel(Request $request, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser();
        if($user->getId() !== $sortie->getOrganisateur()->getId()){
            return $this->redirectToRoute('accueil');
        }

        return $this->render('sortie/cancel.html.twig', []);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function detail(Sortie $sortie) {

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/join', name: 'join', methods: ['GET', 'POST'])]
    public function join(Sortie $sortie, EntityManagerInterface $entityManager, UserRepository $userRepository){

        $currentUser = $this->getUser();
        $now = date("Y-m-d H:i:s");
        $errors = [];

        if($currentUser->isActif() === false) {

            $errors[] = "Vous ne pouvez pas vous inscrire à cette sortie car votre compte est désactivé";
        }

        if($sortie->getParticipants()->contains($currentUser)){

            $errors[] = "Vous êtes déjà inscrit à cette sortie !";
        }

        if(count($sortie->getParticipants()) >= $sortie->getNbMaxParticipant()) {

            $errors[] = "Vous ne pouvez pas vous inscrire car le nombre maximal de participant est déjà atteint";
        }

        if(strtotime($now) > $sortie->getDateLimitInscription()->getTimestamp()) {

            $errors[] = "Vous ne pouvez pas vous inscrire à cette car la date limite d'inscription est dépassée";
        }

        if(count($errors) > 0) {
            foreach($errors as $error) {
                $this->addFlash("error", $error);
            }
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        $sortie->addParticipant($currentUser);
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash("success", "Vous êtes bien inscrit à l'évènement {$sortie->getNom()} !");
        return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
    }

}
