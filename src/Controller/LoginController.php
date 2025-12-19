<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(Request $request,UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();

        $form = $this->createForm(LoginType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $username = $user->getUsername();
            $password = $user->getPassword();

            $userDB = $userRepository->findOneBy(['username' => $username]);

            if ($passwordHasher->isPasswordValid($userDB, $password)) {
                $_SESSION['UserID'] = $userDB->getId();
                return $this->redirectToRoute('app_home_page');
            }
        }

        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
            'Login' => $form,
        ]);
    }

    #[Route('login/reset', name: 'password_reset')]
    public function PassReset(Request $request,UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher,)
    {
        return $this->render('login/PassReset.html.twig', [

        ]);
    }
}
