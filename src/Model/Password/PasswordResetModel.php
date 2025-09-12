<?php

declare(strict_types=1);

namespace App\Model\Password;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordResetModel
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[OA\Property(
        description: 'Reset token of user',
        type: 'string',
        example: '9E4PrHk1sHLCs4ruM3k7v-mgGNWdecm9yhi1RLZ491k'
    )
    ]
    private ?string $token = null;

    #[Assert\Length(min: 4)]
    #[OA\Property(description: 'Plaintext password', type: 'string', example: 'user-password')]
    private ?string $password = null;

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
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
