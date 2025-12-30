<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
final class RegisterUser
{
    public function __construct(
        private AuthLogger $logger,
        private UserRepository $users,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
        private PassStrCheck $passStrCheck,
    ) {}

    public function register(string $username, string $plainPassword): RegisterResult
    {
        if ($this->users->findOneBy(['username' => $username])) {
            $this->logger->log('register_attempt', false, $username, 'already_exists');
            return RegisterResult::fail('Username already exists.');
        }

        $strength = $this->passStrCheck->check($plainPassword);
        if (!$strength['valid']) {
            $this->logger->log('register_attempt', false, $username, 'weak_password');
            return RegisterResult::fail('Password too weak.');
        }

        $user = new User()
            ->setUsername($username)
            ->setIsActive(true);

        $user->setPassword($this->hasher->hashPassword($user, $plainPassword));

        $this->em->persist($user);
        $this->em->flush();

        $this->logger->log('register_attempt', true, $username, null, $user);

        return RegisterResult::ok($user);
    }
}
