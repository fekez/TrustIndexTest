<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReviewRepositoryIntegrationTest extends KernelTestCase
{
    private ReviewRepository $repository;
    private \Doctrine\ORM\EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();

        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        \assert($entityManager instanceof \Doctrine\ORM\EntityManagerInterface);
        $this->em = $entityManager;

        $this->repository = $this->em->getRepository(Review::class);

        $this->em->createQuery('DELETE FROM App\Entity\Review r')->execute();

        /** @var \Symfony\Contracts\Cache\CacheInterface $cache */
        $cache = static::getContainer()->get('cache.app');
        $cache->clear();
    }

    private function createReview(string $company, int $rating): Review
    {
        $review = new Review();
        $review->setCompanyName($company);
        $review->setRating($rating);
        $review->setReviewText('Teszt vélemény szövege.');
        $review->setAuthorEmail('test@example.com');
        $this->em->persist($review);

        return $review;
    }

    public function testGetCompanyStatsReturnsCorrectData(): void
    {
        $this->createReview('Acme Corp', 5);
        $this->createReview('Acme Corp', 3);
        $this->createReview('Beta Solutions', 4);
        $this->em->flush();

        $stats = $this->repository->getCompanyStats();

        $this->assertCount(2, $stats);

        // Acme: avg 4.0, Beta: avg 4.0 – sorrend: Acme előbb (egyenlő esetén DB sorrend)
        $companies = array_column($stats, 'company_name');
        $this->assertContains('Acme Corp', $companies);
        $this->assertContains('Beta Solutions', $companies);

        $acme = array_values(array_filter($stats, static fn ($r) => 'Acme Corp' === $r['company_name']))[0];
        $this->assertSame(2, $acme['review_count']);
        $this->assertSame(4.0, $acme['average_rating']);
    }

    public function testGetCompanyStatsOrderedByAverageDescending(): void
    {
        $this->createReview('Low Corp', 1);
        $this->createReview('High Corp', 5);
        $this->createReview('Mid Corp', 3);
        $this->em->flush();

        $stats = $this->repository->getCompanyStats();

        $this->assertSame('High Corp', $stats[0]['company_name']);
        $this->assertSame('Mid Corp', $stats[1]['company_name']);
        $this->assertSame('Low Corp', $stats[2]['company_name']);
    }

    public function testGetCompanyStatsWithSearchFindsExactMatch(): void
    {
        $this->createReview('Acme Corp', 5);
        $this->createReview('Beta Solutions', 4);
        $this->em->flush();

        $stats = $this->repository->getCompanyStats('Acme');

        $this->assertCount(1, $stats);
        $this->assertSame('Acme Corp', $stats[0]['company_name']);
    }

    public function testGetCompanyStatsWithSearchIsCaseInsensitive(): void
    {
        $this->createReview('Acme Corp', 5);
        $this->createReview('Beta Solutions', 4);
        $this->em->flush();

        $stats = $this->repository->getCompanyStats('acme');

        $this->assertCount(1, $stats);
        $this->assertSame('Acme Corp', $stats[0]['company_name']);
    }

    public function testGetCompanyStatsWithSearchFindsPartialMatch(): void
    {
        $this->createReview('Acme Corp', 5);
        $this->createReview('Acme Solutions', 4);
        $this->createReview('Beta Ltd', 3);
        $this->em->flush();

        $stats = $this->repository->getCompanyStats('acme');

        $this->assertCount(2, $stats);
        $companies = array_column($stats, 'company_name');
        $this->assertContains('Acme Corp', $companies);
        $this->assertContains('Acme Solutions', $companies);
    }

    public function testGetCompanyStatsWithSearchReturnsEmptyForNoMatch(): void
    {
        $this->createReview('Acme Corp', 5);
        $this->em->flush();

        $stats = $this->repository->getCompanyStats('nonexistent');

        $this->assertCount(0, $stats);
    }

    public function testGetCompanyStatsWithNullSearchReturnsAll(): void
    {
        $this->createReview('Acme Corp', 5);
        $this->createReview('Beta Solutions', 4);
        $this->em->flush();

        $stats = $this->repository->getCompanyStats(null);

        $this->assertCount(2, $stats);
    }

    public function testGetCompanyStatsEmptyDatabase(): void
    {
        $stats = $this->repository->getCompanyStats();

        $this->assertCount(0, $stats);
        $this->assertIsArray($stats);
    }
}
