<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class emailAdder
{
    public function __construct(
        private ClockInterface $clock,
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private RequestStack $requestStack,
    ){}

    public function addEmail(string $input, $userData, $state): array
    {
        $session = $this->requestStack->getSession();
        $error = null;

        if ($state === true) {
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

                if ($timeSent->add(new \DateInterval('PT10M')) <= $now) {
                    $error = 'Expired code';
                    return [
                        'emailSent' => true,
                        'emailConfirmed' => false,
                        'error' => $error,
                    ];
                } else {
                    $now = $this->clock->now();

                    $email = $session->get('tempEmail');

                    $userData->setEmail($email);
                    $userData->setEmailVerifiedAt($now);

                    $this->em->flush();

                    return [
                        'emailSent' => true,
                        'emailConfirmed' => true,
                        'email' => $email,
                        'error' => $error,
                    ];
                }
            } else {
                $error = 'Wrong code';
                return [
                    'emailSent' => true,
                    'emailConfirmed' => false,
                    'error' => $error,
                ];
            }
        }

        return [
            'emailSent' => false,
            'emailConfirmed' => false,
            'error' => $error,
        ];
    }
}
