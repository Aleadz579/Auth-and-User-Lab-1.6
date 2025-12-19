<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function index(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $user = new User();

        $form = $this->createForm(LoginType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $username = $user->getUsername();
            $password = $user->getPassword();

            $userDB = $userRepository->findOneBy(['username' => $username]);

            if(!$userDB) {
                $user->setUsername($username);
                $user->setPassword($password);

                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('app_login');
            }


        }
        return $this->render('register/index.html.twig', [
            'controller_name' => 'RegisterController',
            'Register' => $form,
        ]);
    }
}
