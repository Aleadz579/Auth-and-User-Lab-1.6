<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class NewPassword
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em,
        private PassStrCheck $passStrCheck,
        private ResetTokenCheck $check,
        private PasswordResetTokenRepository $TokenRepo,
    ) {}

    public function changePassword(string $URLToken, string $newPassword)
    {
        $strength = $this->passStrCheck->check($newPassword);
        [$TokenCheck, $Token] = $this->check->tokenCheck($URLToken);

        if (!$TokenCheck) {
            return NewPasswordResult::isChanged(false, 'Invalid token.');
        }

        if (!$strength['valid']) {
            return NewPasswordResult::isChanged(false,'Password too weak.');
        }

        $user = $Token->getUser();

        $hashed = $this->passwordHasher->hashPassword($user, $newPassword);

        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();

        $Token->setConsumedAt(new \DateTimeImmutable());

        return NewPasswordResult::isChanged(true);
    }
}
