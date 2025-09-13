<?php

declare(strict_types=1);

namespace App\Model\User;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class RecoverAccountModel
{
    #[Assert\Email]
    #[OA\Property(description: 'Email of user', type: 'string', example: 'john.doe@mail.com')]
    private ?string $username = null;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }
}
