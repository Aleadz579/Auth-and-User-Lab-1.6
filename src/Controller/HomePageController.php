<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Service\emailAdder;
final class HomePageController extends AbstractController
{

    #[Route('/homepage', name: 'app_home_page', methods: ['GET','POST'])]
    public function index(UserRepository $userRepository, Request $request, emailAdder $emailAdder): Response
    {
        $emailAdded = false;
        $hasEmail = false;
        $userData = $this->getUser();
        $SessionUsername = $userData->getUsername();
        $SessionRole = $userData->getRoles();


        assert($userData instanceof \App\Entity\User);

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $email = (string) $request->request->get('email');
            $code = (string) $request->request->get('code');

            if ($userData->getEmail() == null || $userData->getEmail() == '') {
                if($userRepository->findOneBy(['email' => $email]) !== null) {
                    return new JsonResponse(['emailExists' => $emailExists = true]);
                }else {
                    if ($email) {
                        $emailAdded = $emailAdder->addEmail($email, $userData, $sent = true);
                    }else if ($code) {
                        $emailAdded = $emailAdder->addEmail($email, $userData, $sent = false);
                    }
                }
            }
            return new JsonResponse(['emailAdded' => $emailAdded]);
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
