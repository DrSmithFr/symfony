<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    private const EMAIL_FROM = 'noreply@lahaut.fr';

    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    private function getNoReplyTemplateEmail(): TemplatedEmail
    {
        $email = (new TemplatedEmail())
            ->from(self::EMAIL_FROM);

        // this non-standard header tells compliant autoresponders ("email holiday mode") to not
        // reply to this message because it's an automated email
        $email
            ->getHeaders()
            ->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

        return $email;
    }

    /**
     * @param User $user
     *
     * @return void
     * @throws TransportExceptionInterface
     */
    public function sendResetPasswordMail(User $user): void
    {
        $email = $this
            ->getNoReplyTemplateEmail()
            ->to($user->getEmail())
            ->subject('Reset your password')
            ->htmlTemplate('emails/reset_password.html.twig')
            ->context([
                'user' => $user
            ]);

        $this->mailer->send($email);
    }
}
