<?php

namespace App\Controller;

use App\Service\RegisterUser;
use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function index(RegisterUser $registerUserService, Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $user->getUsername();
            $plainPassword = $form->get('plainPassword')->getData();

            $result = $registerUserService->register($username, $plainPassword);

            if (!$result->success) {
                $this->addFlash('error', 'Username already exists.');
                return $this->redirectToRoute('app_register');
            }
            return $this->redirectToRoute('app_login');
        }
        return $this->render('register/index.html.twig', [
            'controller_name' => 'RegisterController',
            'Register' => $form,
        ]);
    }
}
