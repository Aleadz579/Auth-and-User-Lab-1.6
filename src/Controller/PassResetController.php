<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\PasswordReset;
use App\Service\NewPassword;
use App\Service\AuthLogger;


final class PassResetController extends AbstractController
{
    #[Route('/login/reset', name: 'password_reset')]
    public function PassReset(PasswordReset $passwordResetService, Request $request,AuthLogger $logger): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $csrftoken  = (string) $request->request->get('_token');

            if (!$this->isCsrfTokenValid('reset_email', $csrftoken)) {
                $logger->log('csrf_attempt', false);
                return $this->json(['error' => 'csrf'], 400);
            }

            $result = $passwordResetService->resetPassword($email);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['result' => $result]);
            }
            return $this->redirectToRoute('password_reset', [], 303);
        }
        return $this->render('pass_reset/PassReset.html.twig');
    }

    #[Route('/login/newpass/{token<[a-f0-9]{32}\.[a-f0-9]{64}>}', name: 'new_password')]
    public function NewPassword(string $token, Request $request, NewPassword $newPasswordService, AuthLogger $logger): Response
    {

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $csrftoken  = (string) $request->request->get('_token');

            if (!$this->isCsrfTokenValid('new_password', $csrftoken)) {
                $logger->log('csrf_attempt', false);
                return $this->json(['error' => 'csrf'], 400);
            }

            if($request->request->get('Pass1') === $request->request->get('Pass2'))
            {
                $newPassword = $request->request->get('Pass1');
                $result = $newPasswordService->changePassword($token, $newPassword);

                return new JsonResponse(['result' => $result]);
            }
        }
        return $this->render('pass_reset/NewPass.html.twig');
    }
}
