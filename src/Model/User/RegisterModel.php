<?php

declare(strict_types=1);

namespace App\Model\User;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterModel
{
    #[Assert\Email]
    #[OA\Property(description: 'Email of user', type: 'string', example: 'john.doe@mail.com')]
    private ?string $username = null;

    #[Assert\Length(min: 4)]
    #[OA\Property(description: 'Plaintext password', type: 'string')]
    private ?string $password = null;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }
}
