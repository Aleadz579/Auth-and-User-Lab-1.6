<?php

namespace App\Service;

use App\Repository\PasswordResetTokenRepository;

class ResetTokenCheck
{
    public function __construct(private PasswordResetTokenRepository $TokenRepo){}
    public function tokenCheck($UrlToken) : bool
    {
        [$selector, $verifier] = explode('.', $UrlToken, 2);

        $reset = $this->TokenRepo->findValidBySelector($selector);

        if (!$reset || $reset->isExpired() || $reset->isConsumed()) {
            return false;
        }

        if (!password_verify($verifier, $reset->getHashedVerifier())) {
            return false;
        }
        return true;
    }
}
