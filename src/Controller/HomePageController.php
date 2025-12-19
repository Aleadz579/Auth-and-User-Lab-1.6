<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
final class HomePageController extends AbstractController
{
    #[Route('/homepage', name: 'app_home_page', methods: ['GET','POST'])]
    public function index(UserRepository $userRepository, Request $request, EntityManagerInterface $em): Response
    {
        $id = $request->getSession()->get('UserID');
        $hasEmail = false;
        $userData = $userRepository->findOneBy(['id' => $id]);

        $SessionUsername = $userData->getUsername();
        $SessionRole = $userData->getRoles();





        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $email = (string) $request->request->get('email');

            if ($userData->getEmail() == null && $userData->getEmail() == '') {
                $userData->setEmail($email);
                $hasEmail = true;
                $em->flush();

            }
            else {

            }
            return new Response('ok');
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
