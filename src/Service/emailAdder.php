<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class emailAdder
{
    public function __construct(
        private ClockInterface $clock,
        //private EntityManagerInterface $em,
        private MailerInterface $mailer,
    ){}

    public function addEmail(string $email, $userData, $state): array
    {
        $now = $this->clock->now();
        $code = random_int(100000, 999999);
        $emaiLConfirm = $userData->getEmail();

        //$userData->setEmail($email);

        if () {
            $email = (new Email())
                ->from('leadzauthlab@gmail.com')
                ->to($email)
                ->subject('Confirmation Email')
                ->html('<header>Verify your email</header>
                              <p>Enter this code to confirm this is the Right email</p>
                              <p>' . $code . '</p>');
            $this->mailer->send($email);

            return [
                'emailSent' => true,
                'emailConfirmed' => false,
            ];
        }


        //$userData->setEmailVerifiedAt($now);

        //$this->em->flush();
        $emailAdded = true;

        return [true, true];
    }
}
