<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Service\EmailAdder;
use App\Service\AuthLogger;
use Doctrine\ORM\EntityManagerInterface;
final class HomePageController extends AbstractController
{

    #[Route('/homepage', name: 'app_home_page', methods: ['GET','POST'])]
    public function index(UserRepository $userRepository, Request $request, EmailAdder $EmailAdder, AuthLogger $logger, EntityManagerInterface $em): Response
    {
        $hasEmail = false;
        $userData = $this->getUser();
        $SessionUsername = $userData->getUsername();
        $SessionRole = $userData->getRoles();


        assert($userData instanceof \App\Entity\User);

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $email = (string) $request->request->get('email');
            $code = (string) $request->request->get('code');
            $delete = $request->request->get('delete') === '1';
            $token  = (string) $request->request->get('_token');

            if ($delete) {
                if (!$this->isCsrfTokenValid('delete_email', $token)) {
                    $logger->log('csrf_attempt', false);
                    return $this->json(['error' => 'csrf'], 400);
                }
                $userData->setEmail(null);
                $userData->setEmailVerifiedAt(null);
                $em->flush();
                return new JsonResponse(['deleted' => true, 'hasEmail' => false]);
            }

            if ($userData->getEmail() == null || $userData->getEmail() == '') {
                if($userRepository->findOneBy(['email' => $email]) !== null) {
                    $logger->log('email_add_attempt', false, $email, 'email_exists', $userData);
                    return new JsonResponse(['emailExists' => true]);
                }else {
                    if ($email) {
                        if (!$this->isCsrfTokenValid('add_email', $token)) {
                            $logger->log('csrf_attempt', false);
                            return $this->json(['error' => 'csrf'], 400);
                        }

                        $emailAdded = $EmailAdder->addEmail($email, $userData,true);

                        return new JsonResponse($emailAdded);
                    }else if ($code) {
                        if (!$this->isCsrfTokenValid('confirm_email', $token)) {
                            $logger->log('csrf_attempt', false);
                            return $this->json(['error' => 'csrf'], 400);
                        }

                        $emailAdded = $EmailAdder->addEmail($code, $userData,false);

                        return new JsonResponse($emailAdded);
                    }
                }
            }
            return new JsonResponse(['hasEmail' => true]);
        }

        if($userData->getEmail() !== null && $userData->getEmail() !== '') {
            $hasEmail = true;
        }
        return $this->render('home_page/index.html.twig', [
            'controller_name' => 'HomePageController',
            'Username' => $SessionUsername,
            'Roles' => $SessionRole,
            'hasEmail' => $hasEmail,
            'email' => $userData->getEmail()


        ]);
    }
}
