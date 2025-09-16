<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\EpisodeRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;

#[ORM\Entity(repositoryClass: EpisodeRepository::class)]
#[ORM\Table(name: 'episodes')]
class Episode
{
    use BlameableEntity;
    use TimestampableEntity;

    #[
        JMS\Expose,
        JMS\Type('integer')
    ]
    #[
        ORM\Id,
        ORM\Column(type: 'integer'),
        ORM\GeneratedValue(strategy: 'AUTO')
    ]
    protected ?int $id = null;

    #[
        JMS\Expose,
        JMS\Type('string')
    ]
    #[ORM\Column(type: 'string')]
    protected ?string $code = null;

    #[
        JMS\Expose,
        JMS\Type('integer')
    ]
    #[ORM\Column(type: 'integer')]
    protected ?int $rank = null;

    #[
        JMS\Expose,
        JMS\Type('string')
    ]
    #[ORM\Column(type: 'string')]
    protected ?string $name = null;

    #[
        JMS\Expose,
        JMS\Type('integer')
    ]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $duration = null;

    #[
        JMS\Expose,
        JMS\Type('string')
    ]
    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $importCode = null;

    #[JMS\Exclude]
    #[
        ORM\ManyToOne(
            targetEntity: Season::class,
            cascade: ['persist'],
            inversedBy: 'episodes'
        ),
        ORM\JoinColumn(
            name: 'season_id',
            referencedColumnName: 'id'
        )
    ]
    protected ?Season $season = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getImportCode(): ?string
    {
        return $this->importCode;
    }

    public function setImportCode(?string $importCode): self
    {
        $this->importCode = $importCode;

        return $this;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): self
    {
        $this->season = $season;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('[%d] - %s', $this->rank, $this->name);
    }
}
