<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\PasswordReset;
use App\Service\NewPassword;

final class PassResetController extends AbstractController
{
    #[Route('/login/reset', name: 'password_reset')]
    public function PassReset(PasswordReset $passwordResetService, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            $result = $passwordResetService->resetPassword($email);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['result' => $result]);
            }
            return $this->redirectToRoute('password_reset', [], 303);
        }
        return $this->render('pass_reset/PassReset.html.twig');
    }

    #[Route('/login/newpass', name: 'new_password')]
    public function NewPassword(Request $request, NewPassword $newPasswordService): Response
    {
        $token = $request->query->get('token');

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
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
