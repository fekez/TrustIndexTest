<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Review;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $fixtures = [
            [
                'company_name' => 'Acme Corp',
                'rating' => 5,
                'review_text' => 'Kivalo szolgaltatas, mindent a legmagasabb szinten oldottak meg. Hatarozottan ajanlom!',
                'author_email' => 'kovacs.peter@example.com',
            ],
            [
                'company_name' => 'Acme Corp',
                'rating' => 4,
                'review_text' => 'Nagyon elegedett vagyok a munkajukkal. Gyors, megbizhato es preciz csapat.',
                'author_email' => 'nagy.anna@example.com',
            ],
            [
                'company_name' => 'Acme Corp',
                'rating' => 5,
                'review_text' => 'Tobb eve dolgozunk egyutt, mindig maximalis elegedettsegel. Profi hozzaallas.',
                'author_email' => 'toth.janos@example.com',
            ],
            [
                'company_name' => 'Beta Solutions',
                'rating' => 3,
                'review_text' => 'Atlagos tapasztalat. A hataridoket tartjak, de a komunikacio neha hagy kivannivaot.',
                'author_email' => 'fekete.maria@example.com',
            ],
            [
                'company_name' => 'Beta Solutions',
                'rating' => 2,
                'review_text' => 'Sajnos nem voltam elegedett. A vallalt munkat kesve adtak at, tobb hibat kellett javittatni.',
                'author_email' => 'varga.laszlo@example.com',
            ],
            [
                'company_name' => 'Gamma Tech',
                'rating' => 5,
                'review_text' => 'Fantasztikus csapat! Innovativ megoldasokkal alltak elo, felulmultak az elvarasainkat.',
                'author_email' => 'horvath.eva@example.com',
            ],
            [
                'company_name' => 'Gamma Tech',
                'rating' => 4,
                'review_text' => 'Szakmailag nagyon felkeszultek, a projekt zokkomenmentesen zajlott le.',
                'author_email' => 'kiss.robert@example.com',
            ],
            [
                'company_name' => 'Delta Services',
                'rating' => 1,
                'review_text' => 'Nagyon rossz tapasztalat. Nem tartjak a hataridoket, az ugyfelszolgalat elerhetetlen.',
                'author_email' => 'molnar.zsuzsanna@example.com',
            ],
            [
                'company_name' => 'Delta Services',
                'rating' => 3,
                'review_text' => 'Vegyes erzesek. Nehany kollega segitokesz volt, de az egesz folyamat tul hosszura nyult.',
                'author_email' => 'balogh.tibor@example.com',
            ],
            [
                'company_name' => 'Gamma Tech',
                'rating' => 5,
                'review_text' => 'Masodszorra is veluk dolgoztunk, ismet kivalo elmeny volt. Megbizhato, rugalmas partner.',
                'author_email' => 'simon.katalin@example.com',
            ],
        ];

        foreach ($fixtures as $data) {
            $review = new Review();
            $review->setCompanyName($data['company_name']);
            $review->setRating($data['rating']);
            $review->setReviewText($data['review_text']);
            $review->setAuthorEmail($data['author_email']);
            $manager->persist($review);
        }

        $manager->flush();
    }
}
