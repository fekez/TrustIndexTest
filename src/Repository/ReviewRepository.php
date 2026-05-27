<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @return Review[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array{company_name: string, review_count: int, average_rating: float}>
     */
    public function getCompanyStats(): array
    {
        /** @var array<int, array{company_name: string, review_count: string, average_rating: string}> $rows */
        $rows = $this->createQueryBuilder('r')
            ->select(
                'r.companyName AS company_name',
                'COUNT(r.id) AS review_count',
                'AVG(r.rating) AS average_rating'
            )
            ->groupBy('r.companyName')
            ->orderBy('average_rating', 'DESC')
            ->getQuery()
            ->getScalarResult();

        return array_map(
            static fn (array $row): array => [
                'company_name' => $row['company_name'],
                'review_count' => (int) $row['review_count'],
                'average_rating' => round((float) $row['average_rating'], 2),
            ],
            $rows
        );
    }
}
