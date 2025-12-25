<?php

namespace App\Service;
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
