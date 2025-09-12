<?php

namespace App\Entity\User;

use App\Entity\Serializable;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use JMS\Serializer\Annotation as JMS;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[Embeddable]
#[JMS\ExclusionPolicy('all')]
class Identity implements Serializable
{
    #[JMS\Expose]
    #[Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[JMS\Type("DateTimeImmutable<'Y-m-d'>")]
    #[OA\Property(type: 'date', example: "1990-12-30")]
    private ?DateTimeImmutable $anniversary;

    #[JMS\Expose]
    #[Assert\NotBlank]
    #[Column(nullable: true)]
    private ?string $firstName;

    #[JMS\Expose]
    #[Assert\NotBlank]
    #[Column(nullable: true)]
    private ?string $lastName;

    #[JMS\Expose]
    #[Assert\NotBlank]
    #[Column(nullable: true)]
    #[OA\Property(example: "fr")]
    private ?string $nationality;

    public function getAnniversary(): ?DateTimeImmutable
    {
        return $this->anniversary;
    }

    public function setAnniversary(?DateTimeImmutable $anniversary): self
    {
        $this->anniversary = $anniversary;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;
        return $this;
    }
}
