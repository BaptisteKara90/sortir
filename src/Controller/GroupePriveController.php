<?php

namespace App\Controller;

use App\Entity\GroupePrive;
use App\Form\AddMemberToGroupeType;
use App\Form\GroupePriveType;
use App\Repository\GroupePriveRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/groupe-prive', name: 'gp_')]
final class GroupePriveController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(GroupePriveRepository $repository): Response
    {
        $groupes = $repository->findAll();

        return $this->render('groupe_prive/list.html.twig', [
            'groupes' => $groupes,
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(Request $request, GroupePriveRepository $repository, EntityManagerInterface $entityManager): Response
    {

        $groupePrive = new GroupePrive();
        $form = $this->createForm(GroupePriveType::class, $groupePrive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupePrive->setProprietaire($this->getUser());
            $groupePrive->addMembre($this->getUser());
            $entityManager->persist($groupePrive);
            try {
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('gp_add');
            }

            $this->addFlash("success", "Le groupe privé {$groupePrive->getNom()} a bien été créé");
            return $this->redirectToRoute('gp_list');
        }

        return $this->render('groupe_prive/add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'detail')]
    public function detail(Request $request, GroupePrive $groupePrive, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(AddMemberToGroupeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form->get('email')->getData();
            $user = $userRepository->findOneByEmail($email);

            if ($user) {
                $groupePrive->addMembre($user);
                $entityManager->persist($groupePrive);
                try {
                    $entityManager->flush();
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                    return $this->redirectToRoute('gp_detail', ['id' => $groupePrive->getId()]);
                }

                $this->addFlash('success', "{$user->getPrenom()} {$user->getNom()} est maintenant membre du groupe {$groupePrive->getNom()}");
                return $this->redirectToRoute('gp_detail', ['id' => $groupePrive->getId()]);
            }
        }

        return $this->render('groupe_prive/detail.html.twig', [
            'groupe' => $groupePrive,
            'form' => $form,
        ]);
    }

}
