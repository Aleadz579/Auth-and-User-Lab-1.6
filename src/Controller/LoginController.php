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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
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
    public function PassReset(MailerInterface $mailer, Request $request, UserRepository $userRepository): Response
    {
        $emailSent = false;

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $userData = $userRepository->findOneBy(['email' => $request->request->get('email')]);
            if (!$userData) {
                $emailExists = true;
                return new JsonResponse(['emailExists' => $emailExists]);
            }

            $link = $this->generateUrl('new_password', ['token' => $userData->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $userName = $userData->getUsername();
            $userEmail = $userData->getEmail();
            $email = (new Email())
                ->from('leadzauthlab@gmail.com')
                ->to($userEmail)
                ->subject($userName)
                ->html('<p>Click here: <a href="' . $link . '">Open the app</a></p>');
            $mailer->send($email);
            $emailSent = true;
            return new JsonResponse(['emailSent' => $emailSent]);
        }


        return $this->render('login/PassReset.html.twig', [
            'emailSent' => $emailSent,
        ]);
    }

    #[Route('/login/newpass', name: 'new_password')]
    public function NewPassword(MailerInterface $mailer, Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $token = $request->query->get('token');

        $user = $userRepository->findOneBy(['id' => $token]);
        $userEmail = $user->getEmail();

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            if($request->request->get('Pass1') === $request->request->get('Pass2'))
            {
                $password = $request->request->get('Pass1');
                $hashed = $passwordHasher->hashPassword($user, $password);

                $user->setPassword($hashed);

                $em->persist($user);
                $em->flush();

                return new JsonResponse(['PasswordChanged' => true]);
            }
        }

        return $this->render('login/NewPass.html.twig', [
            'email' => $userEmail,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): void
    {
        $session = $request->getSession();
        $session->invalidate();
    }

}
