<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Mime\Email;
use App\Repository\AuthEventLogRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class EmailAdder
{
    public function __construct(
        private AuthLogger $logger,
        private ClockInterface $clock,
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private RequestStack $requestStack,
        private AuthEventLogRepository $authLogRepo,
    ){}

    public function addEmail(string $input, $userData, bool $state): array
    {
        $session = $this->requestStack->getSession();
        $error = null;

        if ($state === true) {
            $ip = $this->requestStack->getCurrentRequest()?->getClientIp() ?? 'unknown';
            $failed = $this->authLogRepo->countFailedByIpInLastMinutes('verification_email', $ip, 10);
            $success = $this->authLogRepo->countSuccessByIpInLastMinutes('verification_email', $ip, 10);

            if ($failed+$success >= 5) {
                return [
                    'emailSent' => true,
                    'emailConfirmed' => false,
                    'error' => 'Email sent too often',
                ];
            }

            $code = random_int(100000, 999999);

            $email = (new Email())
                ->from('leadzauthlab@gmail.com')
                ->to($input)
                ->subject('Confirmation Email')
                ->html('<header>Verify your email</header>
                              <p>Enter this code to confirm this is the Right email</p>
                              <p>' . $code . '</p>');

            $this->mailer->send($email);

            $tempEmail = $input;
            $session->set('tempEmail', $tempEmail);
            $session->set('code', $code);
            $session->set('timeSent', $now = $this->clock->now());

            $this->logger->log('verification_email', true, $tempEmail, null, $userData);

            return [
                'emailSent' => true,
                'emailConfirmed' => false,
                'error' => $error,
            ];
        } else if ($state === false) {
            $code = $session->get('code');

            if($input == $code)
            {
                $timeSent = $session->get('timeSent');
                $now = $this->clock->now();

                if (!$timeSent instanceof \DateTimeInterface) {
                    $this->logger->log('code_input', false, null, 'inactive_code', $userData);
                    return [
                        'emailSent' => false,
                        'emailConfirmed' => false,
                        'error' => 'No active code'
                    ];
                }

                if ($timeSent->add(new \DateInterval('PT10M')) <= $now) {
                    $this->logger->log('code_input', false, null, 'expired_code', $userData);
                    return [
                        'emailSent' => true,
                        'emailConfirmed' => false,
                        'error' => 'Expired code',
                    ];
                } else {
                    $now = $this->clock->now();

                    $email = $session->get('tempEmail');

                    $userData->setEmail($email);
                    $userData->setEmailVerifiedAt($now);

                    $this->em->flush();

                    $this->logger->log('verify_email', true, null, null, $userData);

                    return [
                        'emailSent' => true,
                        'emailConfirmed' => true,
                        'email' => $email,
                        'error' => $error,
                    ];
                }
            } else {
                $this->logger->log('verify_email', false, null, 'wrong_code', $userData);

                $ip = $this->requestStack->getCurrentRequest()?->getClientIp() ?? 'unknown';
                $failed = $this->authLogRepo->countFailedByIpInLastMinutes('verify_email', $ip, 10);

                if ($failed >= 5) {
                    return [
                        'emailSent' => true,
                        'emailConfirmed' => false,
                        'error' => 'Too many incorrect attempts',
                    ];
                }

                return [
                    'emailSent' => true,
                    'emailConfirmed' => false,
                    'error' => 'Wrong code',
                ];
            }
        }

        $this->logger->log('add_email', false, null, 'something_else', $userData, [$input, $userData, $state]);
        return [
            'emailSent' => false,
            'emailConfirmed' => false,
            'error' => $error,
        ];
    }
}
