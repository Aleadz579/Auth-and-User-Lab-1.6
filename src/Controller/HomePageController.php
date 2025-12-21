<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
final class HomePageController extends AbstractController
{
    #[Route('/homepage', name: 'app_home_page', methods: ['GET','POST'])]
    public function index(UserRepository $userRepository, Request $request, EntityManagerInterface $em): Response
    {
        if (!$request->getSession()->get('UserID')) {
            return $this->redirectToRoute('app_login');
        }

        $emailAdded = false;
        $emailExists = false;
        $id = $request->getSession()->get('UserID');
        $hasEmail = false;
        $userData = $userRepository->findOneBy(['id' => $id]);
        $SessionUsername = $userData->getUsername();
        $SessionRole = $userData->getRoles();

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $email = (string) $request->request->get('email');

            if ($userData->getEmail() == null || $userData->getEmail() == '') {
                if($userRepository->findOneBy(['email' => $email]) == null){
                    $userData->setEmail($email);

                    $em->flush();
                    $emailAdded = true;
                }else {
                    $emailExists = true;
                }
            }
            return new JsonResponse(['emailAdded' => $emailAdded, 'emailExists' => $emailExists]);
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
