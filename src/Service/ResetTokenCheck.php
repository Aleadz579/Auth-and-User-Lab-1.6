<?php

namespace App\Service;

use App\Repository\PasswordResetTokenRepository;

class ResetTokenCheck
{
    public function __construct(private PasswordResetTokenRepository $TokenRepo){}
    public function tokenCheck($UrlToken) : array
    {
        [$selector, $verifier] = explode('.', $UrlToken, 2);

        $Token = $this->TokenRepo->findOneBy(['selector' => $selector]);

        if (!$Token || $Token->isExpired() || $Token->isConsumed()) {
            return [false, null];
        }

        if (!hash_equals($Token->getHashedVerifier(), hash('sha256', $verifier))) {
            return [false, null];
        }

        $Token->setConsumedAt(new \DateTimeImmutable());
        return [true, $selector, $Token];
    }
}
