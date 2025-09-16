<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Historic;
use App\Entity\Series;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class HistoricRepository
 * @package App\Repository
 * @codeCoverageIgnore
 */
class HistoricRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Historic::class);
    }

    /**
     * @param User   $user
     * @param Series $series
     * @return Historic|null
     * @throws NonUniqueResultException
     */
    public function getHistoricByUserAndSeries(User $user, Series $series):? Historic
    {
        return $this
            ->createQueryBuilder('h')
            ->where('h.user = :user')
            ->andWhere('h.series = :series')
            ->setParameters(
                [
                    'user'   => $user,
                    'series' => $series,
                ]
            )
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllByUser(User $user): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('historic')
            ->addSelect('series')
            ->from(Historic::class, 'historic')
            ->join('historic.series', 'series')
            ->where('historic.user = :user')
            ->setParameter('user', $user)
            ->orderBy('historic.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
