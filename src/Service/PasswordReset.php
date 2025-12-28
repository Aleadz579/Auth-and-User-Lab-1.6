<?php

namespace App\Service;

use App\Entity\PasswordResetToken;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PasswordReset
{
    public function __construct(
        private UserRepository $users,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $entityManager,
    ) {}

    public function resetPassword(string $email): PasswordResetResult
    {
        $userData = $this->users->findOneBy(['email' => $email]);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$userData) {
            return PasswordResetResult::isSent(false);
        }

        $selector = bin2hex(random_bytes(16));
        $verifier = bin2hex(random_bytes(32));
        $UrlToken = $selector . '.' . $verifier;
        $hashedVerifier = hash('sha256', $verifier);
        $RequestedAt = new \DateTimeImmutable();
        $ExpiresAt = $RequestedAt->add(new \DateInterval('PT1H'));

        $token = new PasswordResetToken()
            ->setUser($userData)
            ->setSelector($selector)
            ->setHashedVerifier($hashedVerifier)
            ->setRequestedAt($RequestedAt)
            ->setExpiresAt($ExpiresAt);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $link = $this->urlGenerator->generate('new_password', ['token' => $UrlToken], UrlGeneratorInterface::ABSOLUTE_URL);
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
