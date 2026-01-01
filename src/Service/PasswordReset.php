<?php

namespace App\Service;

use App\Entity\PasswordResetToken;
use App\Repository\AuthEventLogRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class PasswordReset
{
    public function __construct(
        private RequestStack $requestStack,
        private AuthLogger $logger,
        private UserRepository $users,
        private AuthEventLogRepository $authLogRepo,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $entityManager,
    ) {}

    public function resetPassword(string $email): PasswordResetResult
    {
        $userData = $this->users->findOneBy(['email' => $email]);

        $ip = $this->requestStack->getCurrentRequest()?->getClientIp() ?? 'unknown';
        $failed = $this->authLogRepo->countFailedByIpInLastMinutes('password_reset_request', $ip, 10);
        $success = $this->authLogRepo->countSuccessByIpInLastMinutes('password_reset_request', $ip, 10);

        if ($failed+$success >= 5) {
            return PasswordResetResult::isSent(true);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->logger->log('password_reset_request', false, $email, 'invalid_email_format');
            return PasswordResetResult::isSent(true);
        }

        if(!$userData) {
            $this->logger->log('password_reset_request', false, $email, 'email_not_found');
            return PasswordResetResult::isSent(true);
        }

        $selector = bin2hex(random_bytes(16));
        $verifier = bin2hex(random_bytes(32));
        $UrlToken = $selector . '.' . $verifier;
        $hashedVerifier = hash('sha256', $verifier);
        $RequestedAt = new \DateTimeImmutable();
        $ExpiresAt = $RequestedAt->add(new \DateInterval('PT1H'));

        $token = (new PasswordResetToken())
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

        $email = new Email()
            ->from('leadzauthlab@gmail.com')
            ->to($userEmail)
            ->subject($userName)
            ->html('<p>Click here: <a href="' . $link . '">Open the app</a></p>');

        $this->mailer->send($email);

        $this->logger->log('password_reset_request', true, $userEmail);

        return PasswordResetResult::isSent(true);
    }
}
