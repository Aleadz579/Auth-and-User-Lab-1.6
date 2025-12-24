<?php

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PasswordReset
{
    public function __construct(
        private UserRepository $users,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function resetPassword(string $email): PasswordResetResult
    {
        $userData = $this->users->findOneBy(['email' => $email]);
        if($userData === null) {
            return PasswordResetResult::isSent(false, 'Email doesnt Exist');
        }

        $link = $this->urlGenerator->generate('new_password', ['token' => $userData->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $userName = $userData->getUsername();
        $userEmail = $userData->getEmail();

        $email = (new Email())
            ->from('leadzauthlab@gmail.com')
            ->to($userEmail)
            ->subject($userName)
            ->html('<p>Click here: <a href="' . $link . '">Open the app</a></p>');

        $this->mailer->send($email);

        return PasswordResetResult::isSent(true);
    }
}
