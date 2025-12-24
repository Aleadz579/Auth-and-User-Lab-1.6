<?php

namespace App\Service;

use App\Entity\User;

final class RegisterResult
{
    private function __construct(
        public bool $success,
        public ?User $user,
        public ?string $error,
    ) {}

    public static function ok(User $user): self
    {
        return new self(true, $user, null);
    }

    public static function fail(string $error): self
    {
        return new self(false, null, $error);
    }
}
