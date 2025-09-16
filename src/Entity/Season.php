<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
#[ORM\Table(name: 'seasons')]
class Season
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

    #[JMS\Type('integer')]
    #[ORM\Column(type: 'integer')]
    protected ?int $rank = null;

    #[JMS\Type('string')]
    #[ORM\Column(type: 'string')]
    protected ?string $name = null;

    #[JMS\Type('string')]
    #[ORM\Column(
        type: 'string',
        nullable: true
    )]
    protected ?string $importCode = null;

    #[JMS\Exclude]
    #[
        ORM\ManyToOne(
            targetEntity: Series::class,
            cascade: ['persist'],
            inversedBy: 'seasons'
        ),
        ORM\JoinColumn(
            name: 'series_id',
            referencedColumnName: 'id'
        )
    ]
    protected ?Series $series = null;

    #[JMS\Type('ArrayCollection<App\Entity\Episode>')]
    #[ORM\OneToMany(
        targetEntity: Episode::class,
        mappedBy: 'season',
        cascade: ['persist', 'remove']
    )]
    protected Collection $episodes;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return self
     */
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRank(): ?int
    {
        return $this->rank;
    }

    /**
     * @param int|null $rank
     * @return self
     */
    public function setRank(?int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImportCode(): ?string
    {
        return $this->importCode;
    }

    /**
     * @param string|null $importCode
     * @return self
     */
    public function setImportCode(?string $importCode): self
    {
        $this->importCode = $importCode;

        return $this;
    }

    /**
     * @return Series|null
     */
    public function getSeries(): ?Series
    {
        return $this->series;
    }

    /**
     * @param Series|null $series
     * @return self
     */
    public function setSeries(?Series $series): self
    {
        $this->series = $series;

        return $this;
    }

    /**
     * @return Collection<Episode>
     */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    /**
     * @param Collection<Episode> $episodes
     * @return self
     */
    public function setEpisodes(Collection $episodes): self
    {
        $this->episodes = $episodes;

        return $this;
    }

    /**
     * @param Episode $episode
     * @return self
     */
    public function addEpisode(Episode $episode): self
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes->add($episode);
            $episode->setSeason($this);
        }

        return $this;
    }

    /**
     * @param Episode $episode
     * @return self
     */
    public function removeEpisode(Episode $episode): self
    {
        $this->episodes->removeElement($episode);
        $episode->setSeason(null);

        return $this;
    }

    public function __toString()
    {
        return $this->name ?? '';
    }
}
