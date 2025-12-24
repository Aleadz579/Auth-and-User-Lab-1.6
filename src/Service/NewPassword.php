<?php

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class NewPassword
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private PassStrCheck $passStrCheck,
    ) {}

    public function changePassword(string $token, string $newPassword)
    {
        $strength = $this->passStrCheck->check($newPassword);
        $user = $this->userRepository->findOneBy(['id' => $token]);

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
