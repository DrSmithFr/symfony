<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Series;
use App\Repository\SeriesRepository;
use Doctrine\ORM\NonUniqueResultException;
use Elastica\Document;

class IndexerService
{
    private SeriesRepository $repository;

    public function __construct(SeriesRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Series $series
     * @return Document|null
     * @throws NonUniqueResultException
     */
    public function buildSeriesDocument(Series $series): ?Document
    {
        $series = $this->repository->getFullyLoadedSeriesById($series->getId());

        if (null === $series->getType()) {
            return null;
        }

        $episodes = [];
        foreach ($series->getSeasons() as $season) {
            foreach ($season->getEpisodes() as $episode) {
                $episodes[] = $episode;
            }
        }

        $categories = [];
        foreach ($series->getCategories() as $category) {
            $categories[] = $category->getName();
        }

        if (count($episodes) === 0) {
            return null;
        }

        return new Document(
            $series->getImportCode(), // Manually defined ID
            [
                'id'          => $series->getImportCode(),
                'locale'      => $series->getLocale(),
                'name'        => $series->getName(),
                'image'       => $series->getImage() ?? '',
                'type'        => $series->getType()->getName(),
                'description' => $series->getDescription(),
                'categories'  => $categories,
                'tags'        => $series->getTags() ?? [],
                'seasons'     => $series->getSeasons()->count(),
                'episodes'    => count($episodes),
                'import_date' => $series->getCreatedAt()->getTimestamp()
            ],
            'series' // Types are deprecated, to be removed in Elastic 7
        );
    }
}
