<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReviewPersistenceTest extends KernelTestCase
{
    private ReviewRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $em->getRepository(Review::class);

        // Clean slate minden tesztnél
        $em->createQuery('DELETE FROM App\Entity\Review r')->execute();
    }

    public function testReviewCanBePersistedAndRetrieved(): void
    {
        $em = static::getContainer()->get('doctrine.orm.entity_manager');

        $review = new Review();
        $review->setCompanyName('Test Corp');
        $review->setRating(4);
        $review->setReviewText('Nagyon jó szolgáltatás.');
        $review->setAuthorEmail('test@example.com');

        $em->persist($review);
        $em->flush();
        $em->clear();

        $found = $this->repository->find($review->getId());

        $this->assertNotNull($found);
        $this->assertSame('Test Corp', $found->getCompanyName());
        $this->assertSame(4, $found->getRating());
        $this->assertSame('Nagyon jó szolgáltatás.', $found->getReviewText());
        $this->assertSame('test@example.com', $found->getAuthorEmail());
        $this->assertInstanceOf(\DateTimeImmutable::class, $found->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $found->getUpdatedAt());
    }

    public function testAllFieldsArePersisted(): void
    {
        $em = static::getContainer()->get('doctrine.orm.entity_manager');

        $review = new Review();
        $review->setCompanyName('Schema Check Inc');
        $review->setRating(1);
        $review->setReviewText('Rövid szöveg.');
        $review->setAuthorEmail('schema@example.com');

        $em->persist($review);
        $em->flush();

        $this->assertNotNull($review->getId());
        $this->assertGreaterThan(0, $review->getId());
    }
}
