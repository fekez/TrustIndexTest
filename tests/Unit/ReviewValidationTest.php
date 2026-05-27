<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Review;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ReviewValidationTest extends TestCase
{
    private function makeValidator(): \Symfony\Component\Validator\Validator\ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    private function validReview(): Review
    {
        $r = new Review();
        $r->setCompanyName('Teszt Kft.');
        $r->setRating(4);
        $r->setReviewText('Kiváló cég, ajánlom mindenkinek.');
        $r->setAuthorEmail('teszt@example.com');

        return $r;
    }

    public function testValidReviewPassesValidation(): void
    {
        $errors = $this->makeValidator()->validate($this->validReview());
        $this->assertCount(0, $errors);
    }

    public function testBlankCompanyNameFails(): void
    {
        $review = $this->validReview();
        $review->setCompanyName('');
        $errors = $this->makeValidator()->validate($review);
        $this->assertGreaterThan(0, \count($errors));
    }

    public function testRatingBelowMinFails(): void
    {
        $review = $this->validReview();
        $review->setRating(0);
        $errors = $this->makeValidator()->validate($review);
        $this->assertGreaterThan(0, \count($errors));
    }

    public function testRatingAboveMaxFails(): void
    {
        $review = $this->validReview();
        $review->setRating(6);
        $errors = $this->makeValidator()->validate($review);
        $this->assertGreaterThan(0, \count($errors));
    }

    public function testRatingBoundaryMinPasses(): void
    {
        $review = $this->validReview();
        $review->setRating(1);
        $errors = $this->makeValidator()->validate($review);
        $this->assertCount(0, $errors);
    }

    public function testRatingBoundaryMaxPasses(): void
    {
        $review = $this->validReview();
        $review->setRating(5);
        $errors = $this->makeValidator()->validate($review);
        $this->assertCount(0, $errors);
    }

    public function testInvalidEmailFails(): void
    {
        $review = $this->validReview();
        $review->setAuthorEmail('nem-email');
        $errors = $this->makeValidator()->validate($review);
        $this->assertGreaterThan(0, \count($errors));
    }

    public function testBlankReviewTextFails(): void
    {
        $review = $this->validReview();
        $review->setReviewText('');
        $errors = $this->makeValidator()->validate($review);
        $this->assertGreaterThan(0, \count($errors));
    }
}
