<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Symfony\Component\Clock\ClockInterface;
use Doctrine\ORM\EntityManagerInterface;
final class HomePageController extends AbstractController
{
    public function __construct(private ClockInterface $clock) {}
    #[Route('/homepage', name: 'app_home_page', methods: ['GET','POST'])]
    public function index(UserRepository $userRepository, Request $request, EntityManagerInterface $em): Response
    {
        $emailAdded = false;
        $emailExists = false;
        $hasEmail = false;
        $userData = $this->getUser();
        $SessionUsername = $userData->getUsername();
        $SessionRole = $userData->getRoles();
        $now = $this->clock->now();

        assert($userData instanceof \App\Entity\User);

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $email = (string) $request->request->get('email');

            if ($userData->getEmail() == null || $userData->getEmail() == '') {
                if($userRepository->findOneBy(['email' => $email]) == null){
                    $userData->setEmail($email);
                    $userData->setEmailVerifiedAt($now);

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
