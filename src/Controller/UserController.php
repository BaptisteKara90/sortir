<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/profile/{id}', name: 'user_profile', methods: ['GET', 'POST'])]
    public function updateUser(User $user, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, FileUploader $fileUploader): Response
    {

        $userForm = $this->createForm(UserType::class, $user);

        $userForm->handleRequest($request);

        if($userForm->isSubmitted() && $userForm->isValid()) {

            $profilePicture = $userForm->get('profilePicture')->getData();

            if($profilePicture) {

                if($user->getProfilePicture()) {
                    $fileUploader->delete($user->getProfilePicture());
                }

                $fileName = $fileUploader->upload($profilePicture);
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
}
