<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ReviewState;
use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: 'review')]
#[ORM\HasLifecycleCallbacks]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'A cég neve nem lehet üres.')]
    #[Assert\Length(max: 255, maxMessage: 'A cég neve legfeljebb 255 karakter lehet.')]
    private ?string $companyName = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\NotNull(message: 'Az értékelés megadása kötelező.')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'Az értékelésnek 1 és 5 között kell lennie.')]
    private int $rating = 5;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'A vélemény szövege nem lehet üres.')]
    private ?string $reviewText = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Az e-mail cím megadása kötelező.')]
    #[Assert\Email(message: 'Érvénytelen e-mail formátum.')]
    private ?string $authorEmail = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::STRING, length: 9, enumType: ReviewState::class, options: ['default' => 'published'])]
    private ReviewState $objectState = ReviewState::Published;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getReviewText(): ?string
    {
        return $this->reviewText;
    }

    public function setReviewText(?string $reviewText): static
    {
        $this->reviewText = $reviewText;

        return $this;
    }

    public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail(?string $authorEmail): static
    {
        $this->authorEmail = $authorEmail;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getObjectState(): ReviewState
    {
        return $this->objectState;
    }

    public function setObjectState(ReviewState $objectState): static
    {
        $this->objectState = $objectState;

        return $this;
    }

    public function isPublished(): bool
    {
        return ReviewState::Published === $this->objectState;
    }

    public function isTrash(): bool
    {
        return ReviewState::Trash === $this->objectState;
    }
}
