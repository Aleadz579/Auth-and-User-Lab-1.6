<?php

namespace App\Service;

use App\Entity\AuthEventLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;
final class AuthLogger
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private Security $security,
    ) {}

    public function log(
        string $action,
        bool $success,
        ?string $identifier = null,
        ?string $failureReason = null,
        ?User $user = null,
        ?array $context = null
    ): void {
        $request = $this->requestStack->getCurrentRequest();

        $log = new AuthEventLog($action);
        $log->setSuccess($success);
        $log->setIdentifier($identifier);
        $log->setFailureReason($success ? null : $failureReason);
        $log->setContext($context);


        if ($user) {
            $log->setUser($user);
        } else {
            $u = $this->security->getUser();
            if ($u instanceof User) {
                $log->setUser($u);
            }
        }

        // request info
        if ($request) {
            $log->setIp($request->getClientIp());
            $ua = (string) $request->headers->get('User-Agent', '');
            $log->setUserAgent($ua !== '' ? mb_substr($ua, 0, 255) : null);
        }

        $this->em->persist($log);
        $this->em->flush();
    }
}
