<?php

namespace App\Repository;

use App\Entity\Rating;
use App\Entity\User;
use App\Entity\PublicationHasLanguage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rating>
 */
class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    /**
     * Find if a user has rated a specific publication in any language
     */
    public function findByUserAndPublication(User $user, int $publicationId): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.publicationHasLanguage', 'pl')
            ->join('pl.publication', 'p')
            ->where('r.user = :user')
            ->andWhere('p.id = :publicationId')
            ->setParameter('user', $user)
            ->setParameter('publicationId', $publicationId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find if a user has rated a specific publication language edition
     */
    public function findByUserAndPublicationLanguage(User $user, PublicationHasLanguage $publicationHasLanguage): ?Rating
    {
        return $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->andWhere('r.publicationHasLanguage = :publicationHasLanguage')
            ->setParameter('user', $user)
            ->setParameter('publicationHasLanguage', $publicationHasLanguage)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Calculate average rating for a publication language
     */
    public function calculateAverageRating(PublicationHasLanguage $publicationHasLanguage): ?float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avgRating')
            ->where('r.publicationHasLanguage = :publicationHasLanguage')
            ->setParameter('publicationHasLanguage', $publicationHasLanguage)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? $result['avgRating'] : null;
    }

    //    /**
    //     * @return Rating[] Returns an array of Rating objects
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

    //    public function findOneBySomeField($value): ?Rating
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
