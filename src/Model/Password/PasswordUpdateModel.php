<?php

declare(strict_types=1);

namespace App\Model\Password;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordUpdateModel
{
    #[OA\Property(description: 'Current plaintext password', type: 'string', example: 'current-password')]
    private ?string $currentPassword = null;

    #[Assert\Length(min: 4)]
    #[OA\Property(description: 'New plaintext password', type: 'string', example: 'new-password')]
    private ?string $newPassword = null;

    public function getCurrentPassword(): ?string
    {
        return $this->currentPassword;
    }

    public function setCurrentPassword(?string $currentPassword): self
    {
        $this->currentPassword = $currentPassword;
        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): self
    {
        $this->newPassword = $newPassword;
        return $this;
    }
}
