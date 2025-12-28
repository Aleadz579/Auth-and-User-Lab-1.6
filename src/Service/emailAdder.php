<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;
class emailAdder
{
    public function __construct(
        private ClockInterface $clock,
        private EntityManagerInterface $em,

    ){}

    public function addEmail(string $email, $userData): bool
    {
        $now = $this->clock->now();

        $userData->setEmail($email);
        $userData->setEmailVerifiedAt($now);

        $this->em->flush();
        $emailAdded = true;

        return $emailAdded;
    }
}
