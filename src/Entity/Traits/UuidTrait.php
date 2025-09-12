<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait UuidTrait
{
    #[ORM\Id]
    #[JMS\Expose]
    #[JMS\Type('uuid')]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[OA\Property(
        type: 'string',
        example: '1ed82229-3199-6552-afb9-5752dd505444'
    )]
    private UuidInterface|string $uuid;

    public function getUuid(): ?string
    {
        if ($this->uuid instanceof UuidInterface) {
            return $this->uuid->toString();
        }

        return $this->uuid;
    }

    public function setUuid(UuidInterface|string $uuid): self
    {
        if (is_string($uuid)) {
            $this->uuid = Uuid::fromString($uuid);
        } else {
            $this->uuid = $uuid;
        }

        return $this;
    }
}
