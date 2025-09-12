<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use DateTime;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class UserService
{
    private UserPasswordHasherInterface $passwordEncoder;
    private TokenGeneratorInterface $tokenGenerator;

    public function __construct(
        UserPasswordHasherInterface $passwordEncoder,
        TokenGeneratorInterface $tokenGenerator,
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function createUser(string $email, string $password): User
    {
        $user = (new User())
            ->setEmail(strtolower($email))
            ->setPlainPassword($password)
            ->setEnable(true);

        $this->updatePassword($user);

        return $user;
    }

    public function updatePassword(User $user): User
    {
        $encoded = $this->passwordEncoder->hashPassword(
            $user,
            $user->getPlainPassword()
        );

        $user->setPassword($encoded);
        $user->setPlainPassword(null);

        return $user;
    }

    public function generateResetToken(User $user): User
    {
        $user->setPasswordResetToken($this->tokenGenerator->generateToken());
        $user->setPasswordResetTokenValidUntil(new DateTime('+1 hour'));

        return $user;
    }

    public function isTokenValid(User $user, $getToken): bool
    {
        return $user->getPasswordResetToken() === $getToken
            && $user->getPasswordResetTokenValidUntil() > new DateTime();
    }

    public function clearPasswordResetToken(User $user): void
    {
        $user->setPasswordResetToken(null);
        $user->setPasswordResetTokenValidUntil(null);
    }

    public function isPasswordValid(User $user, string $password): bool
    {
        return $this
            ->passwordEncoder
            ->isPasswordValid(
                $user,
                $password
            );
    }
}
