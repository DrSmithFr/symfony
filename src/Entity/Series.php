<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;

#[ORM\Entity(repositoryClass: SeriesRepository::class)]
#[ORM\Table(name: 'series')]
class Series
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
    #[ORM\Column(type: 'string')]
    protected ?string $locale;

    #[JMS\Type('string')]
    #[ORM\Column(
        type: 'string',
        nullable: true
    )]
    protected ?string $image;

    #[JMS\Type('string')]
    #[ORM\Column(type: 'string')]
    protected ?string $description;

    #[JMS\Type('string')]
    #[ORM\Column(
        type: 'string',
        nullable: true
    )]
    protected ?string $importCode;

    #[JMS\Type('App\Entity\SeriesType')]
    #[
        ORM\ManyToOne(
            targetEntity: SeriesType::class,
            cascade: ['persist'],
            inversedBy: 'series'
        ),
        ORM\JoinColumn(
            name: 'series_type_id',
            referencedColumnName: 'id',
            nullable: true
        )
    ]
    protected ?SeriesType $type = null;

    #[JMS\Type('ArrayCollection<App\Entity\Season>')]
    #[ORM\OneToMany(
        targetEntity: Season::class,
        mappedBy: 'series',
        cascade: ['persist', 'remove']
    )]
    protected Collection $seasons;

    #[
        ORM\ManyToMany(
            targetEntity: Category::class,
            inversedBy: 'series'
        ),
        ORM\JoinTable(
            name: 'mtm_series_to_categories',
            joinColumns: [
                new ORM\JoinColumn(
                    name: 'series_id',
                    referencedColumnName: 'id'
                ),
            ],
            inverseJoinColumns: [
                new ORM\JoinColumn(
                    name: 'category_id',
                    referencedColumnName: 'id'
                ),
            ]
        )
    ]
    #[JMS\Type('ArrayCollection<App\Entity\Category>')]
    protected Collection $categories;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    #[JMS\Type('array<string>')]
    protected array $tags = [];

    public function __construct()
    {
        $this->seasons = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->tags = [];
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
     *
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
     *
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
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string|null $locale
     *
     * @return self
     */
    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     *
     * @return self
     */
    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
     *
     * @return self
     */
    public function setImportCode(?string $importCode): self
    {
        $this->importCode = $importCode;

        return $this;
    }

    /**
     * @return SeriesType|null
     */
    public function getType(): ?SeriesType
    {
        return $this->type;
    }

    /**
     * @param SeriesType|null $type
     *
     * @return self
     */
    public function setType(?SeriesType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<Season>
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }

    /**
     * @param Collection<Season> $seasons
     *
     * @return self
     */
    public function setSeasons(Collection $seasons)
    {
        $this->seasons = $seasons;

        return $this;
    }

    /**
     * @return Collection<Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @param Collection<Category> $categories
     *
     * @return self
     */
    public function setCategories(Collection $categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     *
     * @return self
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param Season $season
     *
     * @return self
     */
    public function addSeason(Season $season): self
    {
        $this->seasons->add($season);
        $season->setSeries($this);

        return $this;
    }

    /**
     * @param Season $season
     *
     * @return self
     */
    public function removeSeason(Season $season): self
    {
        $this->seasons->removeElement($season);
        $season->setSeries(null);

        return $this;
    }

    /**
     * @param Category $category
     *
     * @return self
     */
    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * @param Category $category
     *
     * @return self
     */
    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @param string $tag
     *
     * @return self
     */
    public function addTag(string $tag): self
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * @param string $tag
     *
     * @return self
     */
    public function removeTag(string $tag): self
    {
        if (false !== $key = array_search($tag, $this->tags, true)) {
            array_splice($this->tags, $key, 1);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isMovie(): bool
    {
        if ($this->seasons->count() === 1) {
            /** @var Season $seasons */
            $seasons = $this->seasons->first();

            return $seasons->getEpisodes()->count() === 1;
        }

        return false;
    }

    public function isLastEpisode(Episode $last): bool
    {
        /** @var Season|null $seasons */
        if ($seasons = $this->seasons->last()) {
            /** @var Episode|null $episode */
            if ($episode = $seasons->getEpisodes()->last()) {
                return $episode === $last;
            }
        }

        return false;
    }

    public function __toString()
    {
        return $this->name ?? '';
    }
}
