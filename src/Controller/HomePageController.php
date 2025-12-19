<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

final class HomePageController extends AbstractController
{
    #[Route('/homepage', name: 'app_home_page')]
    public function index(UserRepository $userRepository): Response
    {
        session_start();
        $id = $_SESSION['UserID'];

        $userData = $userRepository->findOneBy(['id' => $id]);

        $SessionUsername = $userData->getUsername();
        $SessionRole = $userData->getRoles();

        return $this->render('home_page/index.html.twig', [
            'controller_name' => 'HomePageController',
            'Username' => $SessionUsername,
            'Roles' => $SessionRole,
        ]);
    }
}
