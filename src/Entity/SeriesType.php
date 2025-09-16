<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeriesTypeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Gedmo\Blameable\Traits\BlameableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: SeriesTypeRepository::class)]
#[ORM\Table(name: 'series_type')]
class SeriesType
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
    protected ?int $id;

    #[JMS\Type('string')]
    #[ORM\Column(type: 'string')]
    protected ?string $name;

    #[JMS\Type('string')]
    #[ORM\Column(
        type: 'string',
        nullable: true
    )]
    protected ?string $importCode;

    /**
     * @var Collection<Series[]>
     */
    #[JMS\Exclude]
    #[
        ORM\OneToMany(
            targetEntity: Series::class,
            mappedBy: 'type'
        )
    ]
    protected Collection $series;

    public function __construct()
    {
        $this->series = new ArrayCollection();
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
     * @return Collection<Series>
     */
    public function getSeries(): Collection
    {
        return $this->series;
    }

    /**
     * @param Collection<Series> $series
     * @return self
     */
    public function setSeries(Collection $series): self
    {
        $this->series = $series;
        return $this;
    }

    /**
     * @param Series $series
     * @return self
     */
    public function addSeries(Series $series): self
    {
        $this->series->add($series);
        $series->setType($this);
        return $this;
    }

    /**
     * @param Series $series
     * @return self
     */
    public function removeSeries(Series $series): self
    {
        $this->series->removeElement($series);
        $series->setType(null);
        return $this;
    }

    public function __toString()
    {
        return $this->name ?? '';
    }
}
