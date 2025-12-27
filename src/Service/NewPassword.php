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
        [$TokenCheck, $Selector] = $this->check->tokenCheck($URLToken);
        dump($TokenCheck, $Selector);
        $TokenID = $this->TokenRepo->findOneBy(['selector' => $Selector]);
        $user = $TokenID->getUser();
        if (!$TokenCheck) {
            return NewPasswordResult::isChanged(false, 'Invalid token.');
        }

        if (!$strength['valid']) {
            return NewPasswordResult::isChanged(false,'Password too weak.');
        }

        $hashed = $this->passwordHasher->hashPassword($user, $newPassword);

        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();

        return NewPasswordResult::isChanged(true);
    }
}
