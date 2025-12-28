<?php

namespace App\Service;
final class PasswordResetResult
{
    private function __construct(
        public bool $isSent,

    ) {}

    public static function isSent(bool $isSent): self
    {
        return new self($isSent);
    }

}
