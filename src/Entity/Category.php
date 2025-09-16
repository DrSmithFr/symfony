<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'categories')]
#[JMS\ExclusionPolicy('all')]
class Category
{
    #[
        ORM\Id,
        ORM\Column(type: 'integer'),
        ORM\GeneratedValue(strategy: 'AUTO')
    ]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', unique: true)]
    protected ?string $slug = null;

    #[ORM\Column(type: 'string')]
    protected ?string $name = null;

    #[ORM\ManyToMany(
        targetEntity: Series::class,
        mappedBy: 'categories'
    )]
    protected Collection $series;

    public function __construct()
    {
        $this->series = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

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

    /**
     * @return Collection<Series>
     */
    public function getSeries(): Collection
    {
        return $this->series;
    }

    /**
     * @param Collection<Series> $series
     * @return $this
     */
    public function setSeries(Collection $series): self
    {
        $this->series = $series;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
