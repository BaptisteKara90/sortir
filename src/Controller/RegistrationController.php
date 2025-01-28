<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CsvImportType;
use App\Form\RegistrationFormType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $roles = $form->get('roles')->getData();
            $user->setRoles($roles);
            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/register/csv', name: 'app_csv_register')]
    public function csvRegister(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $site = $form->get('site')->getData();
            $file = $form->get("file")->getData();

            if($file) {

                if (($handle = fopen($file->getPathname(), "r")) !== false) {

                    while (($data = fgetcsv($handle)) !== false) {

                        try {
                            $user = new User();
                            $user->setNom($data[0]);
                            $user->setPrenom($data[1]);
                            $user->setEmail($data[2]);
                            $user->setTelephone($data[3]);
                            $user->setPassword($userPasswordHasher->hashPassword($user, $data[4]));
                        } catch (\Exception $e) {
                            $this->addFlash('error', $e->getMessage());
                            return $this->redirectToRoute('user_list');
                        }

                        $user->setSite($site);
                        $user->setActif(true);
                        $user->setRoles(['ROLE_USER']);

                        $entityManager->persist($user);
                    }
                    fclose($handle);

                    try {
                        $entityManager->flush();
                    } catch (\Exception $e) {
                        $this->addFlash('error', $e->getMessage());
                        return $this->redirectToRoute('user_list');
                    }
                }
            }

            $this->addFlash('success', "l'import des participant a été réalisé avec succès !");
            return $this->redirectToRoute('user_list');
        }

        return $this->render('registration/csv_import.html.twig', [
            'form' => $form,
        ]);
    }
}
