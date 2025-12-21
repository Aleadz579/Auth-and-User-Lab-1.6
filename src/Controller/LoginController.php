<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(Request $request,UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $session = $request->getSession();

        $form = $this->createForm(LoginType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $username = $user->getUsername();
            $password = $user->getPassword();

            $userDB = $userRepository->findOneBy(['username' => $username]);

            if ($passwordHasher->isPasswordValid($userDB, $password)) {
                $session->set('UserID', $userDB->getId());
                return $this->redirectToRoute('app_home_page');
            }
        }

        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
            'Login' => $form,
        ]);
    }

    #[Route('login/reset', name: 'password_reset')]
    public function PassReset(MailerInterface $mailer, Request $request): Response
    {
        $emailSent = false;
        try{
            if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
                $email = (new Email())
                    ->from('leadzauthlab@gmail.com')
                    ->to('engelhardtcarsten00@gmail.com')
                    ->subject('Bastard')
                    ->text('Hund.');
                $mailer->send($email);
                $emailSent = true;
                return new JsonResponse(['emailSent' => $emailSent]);
            }
        } catch (\Throwable $e) {
            return new Response($e->getMessage(), 500);
        }


        return $this->render('login/PassReset.html.twig', [
            'emailSent' => $emailSent,
        ]);
    }
}
