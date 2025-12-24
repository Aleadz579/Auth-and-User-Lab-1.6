<?php

namespace App\Service;

use App\Entity\User;

final class PasswordResetResult
{
    private function __construct(
        public bool $isSent,
        public ?string $error,
    ) {}

    public static function isSent(bool $isSent, ?string $error = null): self
    {
        return new self($isSent, $error);
    }

}
