<?php

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class NewPasswordResult
{
    public function __construct(
        public bool $isChanged,
        public ?string $error,
    ) {}

    public static function isChanged(bool $isChanged, ?string $error = null): self
    {
        return new self($isChanged, $error);
    }
}
