<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Repository\UserRepository;
use App\Service\PassStrCheck;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function index(Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, PassStrCheck $passStrCheck): Response
    {
        $isWeak = false;
        $user = new User();

        $form = $this->createForm(LoginType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $username = $user->getUsername();
            $password = $user->getPassword();

            $result = $passStrCheck->check($password);
            if (!$result['valid']) {
                $this->addFlash('error', 'Password too weak.');
                return $this->redirectToRoute('app_register');
            }

            $userDB = $userRepository->findOneBy(['username' => $username]);
            $hashed = $passwordHasher->hashPassword($userDB, $password);

            if(!$userDB) {
                $user->setUsername($username);
                $user->setPassword($hashed);

                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('app_login');

            } else {
                $this->addFlash('error', 'Username already exists.');
                return $this->redirectToRoute('app_register');
            }
        }
        return $this->render('register/index.html.twig', [
            'controller_name' => 'RegisterController',
            'Register' => $form,
        ]);
    }
}
