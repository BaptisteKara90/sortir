<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\FilterType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Service\SortieDesactivator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/sortie', name: 'sortie_')]
final class SortieController extends AbstractController
{
    #[Route('/{idSite?}', name: 'list', requirements: ['idSite' => '\d+'], methods: ['GET', 'POST'])]
    public function index(SortieRepository $sortieRepository,SiteRepository $siteRepository, Request $request, EntityManagerInterface $entityManager,SortieDesactivator $sortieDesactivator,?int $idSite = null): Response{

        $sortieDesactivator->desactivateOldSorties();

        $user = $this->getUser();
        
        if($idSite != null){
            $site = $siteRepository->find($idSite);
            $formFilter = $this->createForm(FilterType::class, null, [
                'userSite' => $site,
            ]);
        }else{
            $userSite = $user->getSite();
            $formFilter = $this->createForm(FilterType::class, null, [
                'userSite' => $userSite,
            ]);
        }

        $formFilter->handleRequest($request);

        if($formFilter->isSubmitted() && $formFilter->isValid()){
            $data = $formFilter->getData();
            $sortieRepository->findByOption($data,$user);
            return $this->render('sortie/index.html.twig', [
                'sorties' => $sortieRepository->findByOption($data,$user),
                'formFilter' => $formFilter,
            ]);
        }
        if($idSite != null){
            $site = $entityManager->find(Site::class, $idSite);
            $sorties = $sortieRepository->findBySite($site);
            return $this->render('sortie/index.html.twig', [
                'sorties' => $sorties,
                'formFilter' => $formFilter,
            ]);
        }
        $sorties = $sortieRepository->findBySite($this->getUser()->getSite(), $user);
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
            if($form->get('nouveauLieu')->getData()){
                $sortie->setLieu($form->get('nouveauLieu')->getData());
            }else{

                $sortie->setLieu($form->get('lieu')->getData());
            }
            $entityManager->persist($sortie);
            $entityManager->flush();
            return $this->redirectToRoute('accueil');
        }
        return $this->render('sortie/add.html.twig', [
            'sortieForm' => $form,
        ]);
    }
    #[Route('/cancel/{id}', name: 'cancel', methods: ['GET', 'POST'])]
    public function cancel(Request $request,EtatRepository $etatRepository , SortieRepository $sortieRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser();
        $etat = $etatRepository->findOneByLibelle('Annulée');
        $raison = $request->get("raison");
        $actif = $request->get("actif");
        $actif= $actif =="true" ? true : false;

        $etatId = $etat->getId();
        if($user->getId() !== $sortie->getOrganisateur()->getId()){
            return $this->redirectToRoute('accueil');
        }
        $cancel = $sortieRepository->cancel($id, $etatId, $raison, $actif);
        if(!$cancel){
            return $this->redirectToRoute('sortie_detail', ['id' => $id]);
        }
        $sortie = $sortieRepository->find($id);
        return $this->redirectToRoute('sortie_detail', ['id' => $id]);
    }

    #[Route('/detail/{id}', name: 'detail', methods: ['GET'])]
    public function detail(Sortie $sortie) {

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/join', name: 'join', methods: ['GET', 'POST'])]
    public function join(Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository){

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

        if(count($sortie->getParticipants()) >= $sortie->getNbMaxParticipant()) {
            $sortie->setEtat($etatRepository->findOneByLibelle("cloturée"));
        }
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash("success", "Vous êtes bien inscrit à l'évènement {$sortie->getNom()} !");
        return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/quit', name: 'quit', methods: ['GET', 'POST'])]
    public function quit(Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository){

        $currentUser = $this->getUser();
        $now = date("Y-m-d H:i:s");
        $errors = [];

        if(strtotime($now) > $sortie->getDebut()->getTimestamp()) {

            $errors[] = "Vous ne pouvez plus vous désinscrire car l'évènement a déjà débuté";
        }

        if(count($errors) > 0) {
            foreach($errors as $error) {
                $this->addFlash("error", $error);
            }
            return $this->redirectToRoute('sortie_list');
        }

        if(strtotime($now) < $sortie->getDateLimitInscription()->getTimestamp() && $sortie->getEtat()->getLibelle() === "cloturée") {

            $sortie->setEtat($etatRepository->findOneByLibelle("ouverte"));
        }

        $sortie->removeParticipant($currentUser);
        $entityManager->persist($sortie);
        $entityManager->flush();
        $this->addFlash("success", "Vous êtes bien désinscrit de l'évènement {$sortie->getNom()} !");
        return $this->redirectToRoute('sortie_list', ['idSite' => $sortie->getSite()->getId()]);
    }
    #[Route('/update/{id}', name: 'update', methods: ['GET', 'POST'])]
    public function update(Sortie $sortie, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, Request $request)
    {
        $sortie=$sortieRepository->find($sortie->getId());
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }
        return $this->render('sortie/update.html.twig', [
            'sortieForm' => $sortieForm,
            'sortie' => $sortie,
        ]);
    }

}
