<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReviewFormTest extends WebTestCase
{
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
}
