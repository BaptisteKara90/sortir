<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\FilterType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Service\SortieDesactivator;
use App\Service\StateModifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/sortie', name: 'sortie_')]
final class SortieController extends AbstractController
{
    #[Route('/{idSite?}', name: 'list', requirements: ['idSite' => '\d+'], methods: ['GET', 'POST'])]
    public function index(SortieRepository $sortieRepository,SiteRepository $siteRepository, Request $request, EntityManagerInterface $entityManager,SortieDesactivator $sortieDesactivator, stateModifier $stateModifier, ?int $idSite = null): Response{

        $sortieDesactivator->desactivateOldSorties();
        $stateModifier->stateModifier();
        $user = $this->getUser();
        
        if($idSite != null){
            $site = $siteRepository->find($idSite);

        }else{
            $site = $user->getSite();
        }

        $formFilter = $this->createForm(FilterType::class, null, [
            'userSite' => $site,
        ]);

        $formFilter->handleRequest($request);

        if($formFilter->isSubmitted() && $formFilter->isValid()) {
            $data = $formFilter->getData();

            $sorties = $sortieRepository->findByOption($data,$user);

            foreach($sorties as $key => $sortie){
                if(!in_array('ROLE_ADMIN',$user->getRoles())) {
                    if(!empty($sortie->getGroupePrive()) && !$sortie->getGroupePrive()->getMembres()->contains($user)) {
                        unset($sorties[$key]);
                    }
                }
            }

            return $this->render('sortie/list.html.twig', [
                'sorties' => $sorties,
                'formFilter' => $formFilter,
            ]);
        }

        $sorties = $sortieRepository->findBySite($site);

        foreach($sorties as $key => $sortie){
            if(!in_array('ROLE_ADMIN',$user->getRoles())) {
                 if(!empty($sortie->getGroupePrive()) && !$sortie->getGroupePrive()->getMembres()->contains($user)) {
                     unset($sorties[$key]);
                 }
            }
        }

        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'formFilter' => $formFilter,
        ]);
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response {

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $sortie->setOrganisateur($this->getUser());
                if($form->get('nouveauLieu')->get('nom')->getData()){
                    $nouveauLieu = $form->get('nouveauLieu')->getData();
                    if($form->get('nouvelleVille')->get('nom')->getData()) {
                        $nouvelleVille = $form->get('nouvelleVille')->getData();
                        $nouveauLieu->setVille($nouvelleVille);
                       }
                    $entityManager->persist($nouveauLieu);
                    $sortie->setLieu($nouveauLieu);
                }else{
                    $sortie->setLieu($form->get('lieu')->getData());
                }
            } catch (\Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
            $entityManager->persist($sortie);
           try {
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }

            $this->addFlash("success", "La sortie {$sortie->getNom()} a été créé avec succès !");
            return $this->redirectToRoute('accueil');
        }

        return $this->render('sortie/add.html.twig', [
            'sortieForm' => $form,
        ]);
    }

    #[Route('/cancel/{id}', name: 'cancel', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function cancel(Sortie $sortie, Request $request,EtatRepository $etatRepository , SortieRepository $sortieRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $raison = $request->get("raison");
        $actif = $request->get("actif") === "true";

        if($user->getId() !== $sortie->getOrganisateur()->getId()){
            $this->addFlash("error", "Vous n'êtes pas autorisé à annuler cette sortie !");
            return $this->redirectToRoute('accueil');
        }

        $sortie->setEtat($etatRepository->findOneByLibelle('Annulée'));
        try {
            $sortie->setRaison($raison);
            $sortie->setActive($actif);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }
        $entityManager->persist($sortie);
        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        $this->addFlash("success", "La sortie {$sortie->getNom()} a bien été annulée !");
        return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
    }

    #[Route('/detail/{id}', name: 'detail', methods: ['GET'])]
    public function detail(Sortie $sortie) {
        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/join', name: 'join', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function join(Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository){

        $user = $this->getUser();
        $now = date("Y-m-d H:i:s");
        $errors = [];

        if($user->isActif() === false) {

            $errors[] = "Vous ne pouvez pas vous inscrire à cette sortie car votre compte est désactivé";
        }

        if($sortie->getParticipants()->contains($user)){

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

        $sortie->addParticipant($user);

        if(count($sortie->getParticipants()) >= $sortie->getNbMaxParticipant()) {
            $sortie->setEtat($etatRepository->findOneByLibelle("cloturée"));
        }

        $entityManager->persist($sortie);
        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        $this->addFlash("success", "Vous êtes bien inscrit à l'évènement {$sortie->getNom()} !");
        return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/quit', name: 'quit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function quit(Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository){

        $user = $this->getUser();
        $now = new \DateTime("now");

        $errors = [];

        if($now->getTimestamp() > $sortie->getDebut()->getTimestamp()) {
            $errors[] = "Vous ne pouvez plus vous désinscrire car l'évènement a déjà débuté";
        }

        if(count($errors) > 0) {
            foreach($errors as $error) {
                $this->addFlash("error", $error);
            }
            return $this->redirectToRoute('sortie_list');
        }

        if($now->getTimestamp() < $sortie->getDateLimitInscription()->getTimestamp() && $sortie->getEtat()->getLibelle() === "cloturée") {
            $sortie->setEtat($etatRepository->findOneByLibelle("ouverte"));
        }

        $sortie->removeParticipant($user);
        $entityManager->persist($sortie);

        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('sortie_list');
        }

        $this->addFlash("success", "Vous êtes bien désinscrit de l'évènement {$sortie->getNom()} !");
        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/update/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(Sortie $sortie, EntityManagerInterface $entityManager, Request $request, VilleRepository $villeRepository)
    {
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            try {

                if ($sortieForm->get('nouveauLieu')->get('nom')->getData()) {
                    $nouveauLieu = $sortieForm->get('nouveauLieu')->getData();

                    if ($sortieForm->get('nouvelleVille')->getData()) {
                        $nouvelleVille = $sortieForm->get('nouvelleVille')->getData();
                        $nouveauLieu->setVille($nouvelleVille);

                    }

                    $entityManager->persist($nouveauLieu);
                    $sortie->setLieu($nouveauLieu);

                }

            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
            $entityManager->persist($sortie);
            try{
                $entityManager->flush();
            }catch (\Exception $e){
                $this->addFlash('error', $e->getMessage());
            }
            $this->addFlash('succcess', "La sortie {$sortie->getNom()} a correctement été modifiée !");
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }
        return $this->render('sortie/update.html.twig', [
            'sortieForm' => $sortieForm,
            'sortie' => $sortie,
        ]);
    }

    #[Route('/open/{id}', name: 'open', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function open(Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository, SortieRepository $sortieRepository){

        if($sortie->getEtat()->getLibelle() === "Créée") {

            $etat= $etatRepository->findOneByLibelle("Ouverte");
            $sortie->setEtat($etat);
            $entityManager->persist($sortie);
            try {
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }

            $this->addFlash('success', "La sortie {$sortie->getNom()} est maintenant ouverte aux inscriptions !");
            return $this->redirectToRoute('sortie_detail', [
                'id' => $sortie->getId()
            ]);
        }
        return $this->redirectToRoute('sortie_list');
    }
}
