<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Enum\RoleEnum;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /***
     * @param string $identifier
     *
     * @return UserInterface|null
     * @throws NonUniqueResultException
     */
    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return $this->findOneActiveByEmail($identifier);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.email = :mail')
            ->setParameter('mail', strtolower($email))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneActiveByEmail(string $email): ?User
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.email = :mail')
            ->andWhere('u.deletedAt IS NULL')
            ->andWhere('u.enable = true')
            ->setParameter('mail', strtolower($email))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getUserByPasswordResetToken(string $resetToken): ?User
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.passwordResetToken = :token')
            ->setParameter('token', $resetToken)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countWithRole(RoleEnum $role): int
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('count(u)')
            ->from(User::class, 'u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%' . $role->value . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function totalPerDay(RoleEnum $role, int $days)
    {
        $from = (new DateTimeImmutable('now'))
            ->sub(new DateInterval('P' . $days . 'D'))
            ->setTime(0, 0);

        $result = $this
            ->createQueryBuilder('user')
            ->select('CAST(user.createdAt as DATE) as day, COUNT(user.uuid) as total')
            ->groupBy('day')
            ->where('user.roles LIKE :role')
            ->andWhere('user.createdAt >= :from')
            ->setParameter('role', '%' . $role->value . '%')
            ->setParameter('from', $from)
            ->getQuery()
            ->getScalarResult();

        $resultMap = array_reduce(
            $result,
            function (array $carry, array $item) {
                $carry[$item['day']] = $item['total'];
                return $carry;
            },
            []
        );

        $totals = [];

        // Fill missing days
        for ($i = $days; $i >= 0; $i--) {
            $day = $from
                ->add(new DateInterval('P' . $i . 'D'))
                ->format('Y-m-d');

            if (isset($resultMap[$day])) {
                $totals[$day] = $resultMap[$day];
            } else {
                $totals[$day] = "0";
            }
        }

        return array_reverse($totals);
    }
}
