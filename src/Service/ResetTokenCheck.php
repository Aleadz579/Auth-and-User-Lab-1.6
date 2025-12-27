<?php

namespace App\Service;

use App\Repository\PasswordResetTokenRepository;

class ResetTokenCheck
{
    public function __construct(private PasswordResetTokenRepository $TokenRepo){}
    public function tokenCheck($UrlToken) : array
    {
        [$selector, $verifier] = explode('.', $UrlToken, 2);

        $reset = $this->TokenRepo->findOneBy(['selector' => $selector]);

        if (!$reset || $reset->isExpired() || $reset->isConsumed()) {
            return [false, null];
        }

        if (!password_verify($verifier, $reset->getHashedVerifier())) {
            return [false, null];
        }
        return [true, $selector];
    }
}
