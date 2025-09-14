<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\EnableTrait;
use App\Entity\Traits\UuidTrait;
use App\Entity\User\Identity;
use App\Enum\RoleEnum;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use InvalidArgumentException;
use JMS\Serializer\Annotation as JMS;
use OpenApi\Attributes as OA;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: UserRepository::class),
    ORM\Table(name: 'users')
]
#[JMS\ExclusionPolicy('all')]
class User implements
    UserInterface,
    TwoFactorInterface,
    PasswordAuthenticatedUserInterface,
    PasswordHasherAwareInterface,
    Serializable
{
    use UuidTrait;

    use EnableTrait;
    use TimestampableEntity;
    use BlameableEntity;
    use SoftDeleteableEntity;

    #[Assert\Email]
    #[JMS\Type('string')]
    #[ORM\Column(unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    #[JMS\Groups(['Default', 'Login'])]
    private ?string $password = null;

    /**
     * Used internally for login form
     */
    private ?string $plainPassword = null;

    #[ORM\Column(nullable: true)]
    private ?string $salt = null;

    #[JMS\Expose]
    #[JMS\MaxDepth(1)]
    #[ORM\Column(name: 'roles', type: Types::SIMPLE_ARRAY)]
    #[JMS\Type('array<string>')]
    #[OA\Property(
        type: 'string[]',
        example: '["ROLE_USER", "ROLE_ADMIN"]',
    )]
    private array $roles = [];

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $authCode = null;

    #[ORM\Column(nullable: true)]
    private ?string $passwordResetToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $passwordResetTokenValidUntil = null;

    #[JMS\Expose]
    #[Embedded(class: Identity::class)]
    private Identity $identity;

    public function __construct()
    {
        $this->identity = new Identity();
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function getPasswordHasherName(): ?string
    {
        return "harsh";
    }

    /**
     * Removes sensitive data from the user.
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(): void
    {
        $this->setPlainPassword(null);
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        // ensure email is lowercase
        $this->email = strtolower($email);

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

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(?string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string[]|RoleEnum[] $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    public function addRole(string | RoleEnum $role): self
    {
        if (is_string($role) && !RoleEnum::tryFrom($role)) {
            throw new InvalidArgumentException('invalid role');
        }

        if ($role instanceof RoleEnum) {
            $role = $role->value;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string | RoleEnum $role): self
    {
        if (is_string($role) && !RoleEnum::tryFrom($role)) {
            throw new InvalidArgumentException('invalid role');
        }

        if ($role instanceof RoleEnum) {
            $role = $role->value;
        }

        $key = array_search($role, $this->roles, true);

        if ($key !== false) {
            array_splice($this->roles, $key, 1);
        }

        return $this;
    }

    public function hasRole(string | RoleEnum $role): bool
    {
        if (is_string($role) && !RoleEnum::tryFrom($role)) {
            throw new InvalidArgumentException('invalid role');
        }

        if ($role instanceof RoleEnum) {
            $role = $role->value;
        }

        return in_array($role, $this->roles, true);
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function setPasswordResetToken(?string $passwordResetToken): self
    {
        $this->passwordResetToken = $passwordResetToken;

        return $this;
    }

    public function getPasswordResetTokenValidUntil(): ?DateTime
    {
        return $this->passwordResetTokenValidUntil;
    }

    public function setPasswordResetTokenValidUntil(?DateTime $passwordResetTokenValidUntil): self
    {
        $this->passwordResetTokenValidUntil = $passwordResetTokenValidUntil;

        return $this;
    }

    public function getIdentity(): Identity
    {
        return $this->identity;
    }

    public function setIdentity(Identity $identity): self
    {
        $this->identity = $identity;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getEmail();
    }

    public function isEmailAuthEnabled(): bool
    {
        return true;
    }

    public function getEmailAuthRecipient(): string
    {
        return $this->getEmail();
    }

    public function getEmailAuthCode(): string | null
    {
        return $this->authCode;
    }

    public function setEmailAuthCode(string $authCode): void
    {
        $this->authCode = $authCode;
    }
}
