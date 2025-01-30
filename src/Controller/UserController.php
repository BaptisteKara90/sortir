<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFilterType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/profile/{id}', name: 'user_profile', methods: ['GET', 'POST'])]
    public function updateUser(User $user, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, ImageUploader $imageUploader): Response {

        if ($user->getId() !== $this->getUser()->getId() && !in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {

            $this->addFlash('error', "Vous n'êtes pas autorisé à accéder à cette page");
            return $this->redirectToRoute('accueil');
        }

        $userForm = $this->createForm(UserType::class, $user);

        $userForm->handleRequest($request);

        if($userForm->isSubmitted() && $userForm->isValid()) {

            $profilePicture = $userForm->get('profilePicture')->getData();

            if($profilePicture) {

                if($user->getProfilePicture()) {
                    $imageUploader->delete($user->getProfilePicture());
                }

                $fileName = $imageUploader->upload($profilePicture);
                $user->setProfilePicture($fileName);
            }

            /** @var string $plainPassword */
            $plainPassword = $userForm->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash("success", "Vos informations ont correctement été mises à jour !");
            $this->redirectToRoute('accueil');
        }

        return $this->render('user/profile.html.twig', [
            'userForm' => $userForm,
            'user' => $user,
        ]);
    }

    #[Route('/profile/detail/{id}', name: 'user_detail', methods: ['GET'])]
    public function showProfile(UserRepository $userRepository, int $id): Response
    {
        $user = $userRepository->find($id);

        return $this->render('user/profile_detail.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/list', name: 'user_list', methods: ['GET', 'POST'])]
    public function list(Request $request, UserRepository $userRepository): Response {

        $userFilterForm = $this->createForm(UserFilterType::class);
        $userFilterForm->handleRequest($request);

        if ($userFilterForm->isSubmitted() && $userFilterForm->isValid()) {
            $filterValues = (object) $userFilterForm->getData();
            $users = $userRepository->findByFilter($filterValues);

            if (!$filterValues) {
                $users = $userRepository->findBy([], ['nom' => 'ASC']);
            }

            return $this->render('user/list.html.twig', [
                'users' => $users,
                'filter' => $userFilterForm,
            ]);
        } else {

            $users = $userRepository->findBy([], ['nom' => 'ASC']);

            return $this->render('user/list.html.twig', [
                'users' => $users,
                'filter' => $userFilterForm,
            ]);
        }
    }

    #[Route('/user/delete/{id}', name: 'user_delete', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function delete(User $user, EntityManagerInterface $entityManager): Response {

        if(in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {

            $user->setActif(false);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash("success", "Le participant a bien été désactivé");
            return $this->redirectToRoute('user_list');
        } else {

            $this->addFlash("error", "Vous n'êtes pas autoridé à effetuer cette action");
            return $this->redirectToRoute('acceuil');
        }
    }

    #[Route('/user/activate/{id}', name: 'user_activate', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function activate(User $user, EntityManagerInterface $entityManager): Response {

        if(in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {

            $user->setActif(true);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash("success", "Le participant a bien été activé");
            return $this->redirectToRoute('user_list');
        } else {

            $this->addFlash("error", "Vous n'êtes pas autoridé à effetuer cette action");
            return $this->redirectToRoute('acceuil');
        }
    }
}
