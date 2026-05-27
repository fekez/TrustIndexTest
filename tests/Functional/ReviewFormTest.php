<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Review;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReviewFormTest extends WebTestCase
{
    // -------------------------------------------------------------------------
    // M3 – Form + validáció + flash
    // -------------------------------------------------------------------------

    public function testGetIndexReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testGetNewFormReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testValidFormSubmitRedirectsWithFlash(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/review/new');

        $form = $crawler->selectButton('Elküldés')->form([
            'review[companyName]' => 'Teszt Kft.',
            'review[rating]' => '4',
            'review[reviewText]' => 'Nagyon jó cég, ajánlom.',
            'review[authorEmail]' => 'teszt@example.com',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    public function testInvalidFormSubmitShowsErrors(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/review/new');

        $form = $crawler->selectButton('Elküldés')->form([
            'review[companyName]' => '',
            'review[rating]' => '3',
            'review[reviewText]' => '',
            'review[authorEmail]' => 'nem-email',
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorExists('form');
    }

    // -------------------------------------------------------------------------
    // M4 – Részletező + /companies + /health
    // -------------------------------------------------------------------------

    public function testShowReviewReturns200(): void
    {
        $client = static::createClient();

        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = static::getContainer()->get('doctrine.orm.entity_manager');

        $review = new Review();
        $review->setCompanyName('Show Kft.');
        $review->setRating(4);
        $review->setReviewText('Részletező teszt szöveg.');
        $review->setAuthorEmail('show@example.com');
        $em->persist($review);
        $em->flush();

        $id = $review->getId();
        $this->assertNotNull($id);

        $client->request('GET', '/review/'.$id);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h4', 'Show Kft.');
    }

    public function testShowReviewReturns404ForUnknownId(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/999999');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCompaniesPageReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/companies');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testCompaniesSearchReturnsFilteredResults(): void
    {
        $client = static::createClient();

        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = static::getContainer()->get('doctrine.orm.entity_manager');

        $em->createQuery('DELETE FROM App\Entity\Review r')->execute();

        $review = new Review();
        $review->setCompanyName('FilterCorp');
        $review->setRating(5);
        $review->setReviewText('Keresés teszt.');
        $review->setAuthorEmail('filter@example.com');
        $em->persist($review);

        $other = new Review();
        $other->setCompanyName('OtherCo');
        $other->setRating(3);
        $other->setReviewText('Másik cég.');
        $other->setAuthorEmail('other@example.com');
        $em->persist($other);

        $em->flush();

        $client->request('GET', '/companies?q=filter');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', 'FilterCorp');
        $this->assertSelectorTextNotContains('table', 'OtherCo');
    }

    public function testHealthCheckReturnsOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        /** @var string $content */
        $content = $client->getResponse()->getContent();

        /** @var array{status: string, db: string, timestamp: string} $data */
        $data = json_decode($content, true);

        $this->assertSame('ok', $data['status']);
        $this->assertSame('ok', $data['db']);
        $this->assertArrayHasKey('timestamp', $data);
    }
}
