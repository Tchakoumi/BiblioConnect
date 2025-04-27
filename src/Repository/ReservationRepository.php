<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Check if a user has reserved a specific publication language edition
     */
    public function hasUserReservedPublication(int $userId, int $publicationHasLanguageId): bool
    {
        $result = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.reservationPublications', 'rp')
            ->where('r.user = :userId')
            ->andWhere('rp.publication_has_language = :publicationId')
            ->setParameter('userId', $userId)
            ->setParameter('publicationId', $publicationHasLanguageId)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$result > 0;
    }
}
