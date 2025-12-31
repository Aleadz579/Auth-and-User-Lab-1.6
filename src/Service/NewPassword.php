<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class NewPassword
{
    public function __construct(
        private AuthLogger $logger,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em,
        private PassStrCheck $passStrCheck,
        private ResetTokenCheck $check,

    ) {}

    public function changePassword(string $URLToken, string $newPassword)
    {
        $strength = $this->passStrCheck->check($newPassword);
        [$TokenCheck, $Token] = $this->check->tokenCheck($URLToken);

        if (!$TokenCheck) {
            $this->logger->log('passChange_attempt', false, null, 'invalid_token');
            return NewPasswordResult::isChanged(false, 'Invalid token.');
        }

        if (!$strength['valid']) {
            $this->logger->log('passChange_attempt', false, null, 'weak_password', $Token->getUser());
            return NewPasswordResult::isChanged(false,'Password too weak.');
        }

        $user = $Token->getUser();

        $hashed = $this->passwordHasher->hashPassword($user, $newPassword);

        $user->setPassword($hashed);
        $Token->setConsumedAt(new \DateTimeImmutable());

        $this->em->persist($user);
        $this->em->flush();

        $this->logger->log('passChange_attempt', true, $user->getUsername(), null, $user);

        return NewPasswordResult::isChanged(true);
    }
}
