<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Review;
use App\Enum\ReviewState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly CacheInterface $cache)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @return Review[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.objectState = :state')
            ->setParameter('state', ReviewState::Published)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array{company_name: string, review_count: int, average_rating: float}>
     */
    public function getCompanyStats(?string $search = null): array
    {
        $cacheKey = 'company_stats_'.md5((string) $search);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($search): array {
            $item->expiresAfter(300);

            $qb = $this->createQueryBuilder('r')
                ->select(
                    'r.companyName AS company_name',
                    'COUNT(r.id) AS review_count',
                    'AVG(r.rating) AS average_rating'
                )
                ->andWhere('r.objectState = :state')
                ->setParameter('state', ReviewState::Published)
                ->groupBy('r.companyName')
                ->orderBy('average_rating', 'DESC');

            if (null !== $search && '' !== $search) {
                $qb->andWhere('LOWER(r.companyName) LIKE LOWER(:search)')
                    ->setParameter('search', '%'.$search.'%');
            }

            /** @var array<int, array{company_name: string, review_count: string, average_rating: string}> $rows */
            $rows = $qb->getQuery()->getScalarResult();

            return array_map(
                static fn (array $row): array => [
                    'company_name' => $row['company_name'],
                    'review_count' => (int) $row['review_count'],
                    'average_rating' => round((float) $row['average_rating'], 2),
                ],
                $rows
            );
        });
    }
}
