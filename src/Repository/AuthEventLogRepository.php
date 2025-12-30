<?php

namespace App\Repository;

use App\Entity\AuthEventLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuthEventLog>
 */
class AuthEventLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthEventLog::class);
    }

    public function countFailedByIpInLastMinutes(string $action, string $ip, int $minutes): int
    {
        $since = new \DateTimeImmutable("-{$minutes} minutes");

        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.action = :action')
            ->andWhere('l.success = false')
            ->andWhere('l.ip = :ip')
            ->andWhere('l.createdAt >= :since')
            ->setParameter('action', $action)
            ->setParameter('ip', $ip)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

//    /**
//     * @return AuthEventLog[] Returns an array of AuthEventLog objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AuthEventLog
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
