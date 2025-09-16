<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class SeasonRepository
 * @package App\Repository
 * @codeCoverageIgnore
 */
class SeasonRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
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
            ->select('count(s) as seasons')
            ->from(Season::class, 's')
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$count;
    }
}
