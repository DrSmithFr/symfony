<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Episode;
use App\Entity\Series;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class EpisodeRepository
 *
 * @package App\Repository
 * @codeCoverageIgnore
 */
class EpisodeRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }

    /**
     * @return int
     * @throws NonUniqueResultException
     */
    public function countAll(): int
    {
        $count = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('count(e) as episodes')
            ->from(Episode::class, 'e')
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$count;
    }

    /**
     * @param Series $series
     * @param int    $id
     * @return Episode|null
     * @throws NonUniqueResultException
     */
    public function findOneBySeriesAndId(Series $series, int $id): ?Episode
    {
        return $this
            ->createQueryBuilder('episode')
            ->join('episode.season', 'season')
            ->join('season.series', 'series')
            ->where('series = :series')
            ->andWhere('episode.id = :id')
            ->setParameters(
                [
                    'series' => $series,
                    'id'     => $id,
                ]
            )
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Episode[]
     */
    public function findAllWithoutDuration(): array
    {
        return $this
            ->createQueryBuilder('e')
            ->where('e.duration IS NULL')
            ->getQuery()
            ->getResult();
    }
}
