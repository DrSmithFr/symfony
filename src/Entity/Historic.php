<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\HistoricRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;

#[ORM\Entity(repositoryClass: HistoricRepository::class)]
#[ORM\Table(name: 'historic')]
class Historic
{
    use BlameableEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[JMS\Type('integer')]
    protected ?int $id = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    protected ?int $timeCode = 0;

    #[
        ORM\ManyToOne(
            targetEntity: User::class,
            inversedBy: 'histories'
        ),
        ORM\JoinColumn(name: 'user_uuid', referencedColumnName: 'uuid')
    ]
    protected ?User $user = null;

    #[
        ORM\ManyToOne(targetEntity: Series::class),
        ORM\JoinColumn(
            name: 'series_id',
            referencedColumnName: 'id'
        )
    ]
    protected ?Series $series = null;

    #[
        ORM\ManyToOne(targetEntity: Episode::class),
        ORM\JoinColumn(
            name: 'episode_id',
            referencedColumnName: 'id'
        )
    ]
    protected ?Episode $episode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTimeCode(): ?int
    {
        return $this->timeCode;
    }

    public function setTimeCode(?int $timeCode): self
    {
        $this->timeCode = $timeCode;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSeries(): ?Series
    {
        return $this->series;
    }

    public function setSeries(?Series $series): self
    {
        $this->series = $series;

        return $this;
    }

    public function getEpisode(): ?Episode
    {
        return $this->episode;
    }

    public function setEpisode(?Episode $episode): self
    {
        $this->episode = $episode;

        return $this;
    }
}
