<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Serializable;
use JMS\Serializer\Annotation as JMS;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class ApiVersionModel implements Serializable
{
    public function __construct(string $version)
    {
        $this->version = $version;
    }

    #[Assert\Email]
    #[OA\Property(description: 'version', type: 'string', example: '1.2.3')]
    private string $version;

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    #[JMS\VirtualProperty]
    #[JMS\SerializedName('major')]
    #[JMS\Expose]
    public function getMajor(): int
    {
        return (int) explode('.', $this->version)[0];
    }

    #[JMS\VirtualProperty]
    #[JMS\SerializedName('minor')]
    #[JMS\Expose]
    public function getMinor(): int
    {
        return (int) explode('.', $this->version)[1];
    }

    #[JMS\VirtualProperty]
    #[JMS\SerializedName('patch')]
    #[JMS\Expose]
    public function getPatch(): int
    {
        return (int) explode('.', $this->version)[2];
    }

    public function __toString(): string
    {
          return $this->version;
    }
}
