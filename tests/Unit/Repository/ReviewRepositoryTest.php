<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use PHPUnit\Framework\TestCase;

class ReviewRepositoryTest extends TestCase
{
    /**
     * Mivel a getCompanyStats() QueryBuilder-t használ (nem pure PHP logika),
     * az üzleti logikát – az int cast-ot és a round()-ot – közvetlenül teszteljük.
     */
    public function testCompanyStatsRowNormalization(): void
    {
        $rawRow = [
            'company_name' => 'Acme Corp',
            'review_count' => '7',
            'average_rating' => '4.142857',
        ];

        $normalized = $this->normalizeStatsRow($rawRow);

        $this->assertSame('Acme Corp', $normalized['company_name']);
        $this->assertSame(7, $normalized['review_count']);
        $this->assertSame(4.14, $normalized['average_rating']);
    }

    public function testCompanyStatsRowNormalizationSingleReview(): void
    {
        $rawRow = [
            'company_name' => 'Beta Solutions',
            'review_count' => '1',
            'average_rating' => '5.0',
        ];

        $normalized = $this->normalizeStatsRow($rawRow);

        $this->assertSame(1, $normalized['review_count']);
        $this->assertSame(5.0, $normalized['average_rating']);
    }

    public function testCompanyStatsRowNormalizationRoundsCorrectly(): void
    {
        $rawRow = [
            'company_name' => 'Gamma Tech',
            'review_count' => '3',
            'average_rating' => '3.666666',
        ];

        $normalized = $this->normalizeStatsRow($rawRow);

        $this->assertSame(3.67, $normalized['average_rating']);
    }

    public function testCompanyStatsRowNormalizationZeroAverage(): void
    {
        // Edge case: egyetlen review, rating=1
        $rawRow = [
            'company_name' => 'Edge Case Ltd',
            'review_count' => '1',
            'average_rating' => '1.0',
        ];

        $normalized = $this->normalizeStatsRow($rawRow);

        $this->assertSame(1, $normalized['review_count']);
        $this->assertSame(1.0, $normalized['average_rating']);
    }

    public function testCompanyStatsRowNormalizationPerfectScore(): void
    {
        // Edge case: minden review 5-ös
        $rawRow = [
            'company_name' => 'Perfect Corp',
            'review_count' => '100',
            'average_rating' => '5.0',
        ];

        $normalized = $this->normalizeStatsRow($rawRow);

        $this->assertSame(100, $normalized['review_count']);
        $this->assertSame(5.0, $normalized['average_rating']);
    }

    /**
     * @param array{company_name: string, review_count: string, average_rating: string} $row
     *
     * @return array{company_name: string, review_count: int, average_rating: float}
     */
    private function normalizeStatsRow(array $row): array
    {
        return [
            'company_name' => $row['company_name'],
            'review_count' => (int) $row['review_count'],
            'average_rating' => round((float) $row['average_rating'], 2),
        ];
    }
}
