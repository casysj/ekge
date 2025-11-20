<?php

declare(strict_types=1);

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * 메인 배너 엔티티
 */
#[ORM\Entity]
#[ORM\Table(name: 'banners')]
#[ORM\HasLifecycleCallbacks]
class Banner
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 200)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 500, name: 'imagePath')]
    private string $imagePath;

    #[ORM\Column(type: 'string', length: 500, name: 'linkUrl', nullable: true)]
    private ?string $linkUrl = null;

    #[ORM\Column(type: 'integer', name: 'displayOrder', options: ['default' => 0])]
    private int $displayOrder = 0;

    #[ORM\Column(type: 'boolean', name: 'isActive', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'date', name: 'startDate', nullable: true)]
    private ?DateTime $startDate = null;

    #[ORM\Column(type: 'date', name: 'endDate', nullable: true)]
    private ?DateTime $endDate = null;

    #[ORM\Column(type: 'datetime', name: 'createdAt')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', name: 'updatedAt')]
    private DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTime();
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): self
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getLinkUrl(): ?string
    {
        return $this->linkUrl;
    }

    public function setLinkUrl(?string $linkUrl): self
    {
        $this->linkUrl = $linkUrl;
        return $this;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): self
    {
        $this->displayOrder = $displayOrder;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTime $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTime $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * 현재 날짜가 배너 활성 기간 내인지 확인
     */
    public function isInPeriod(?DateTime $now = null): bool
    {
        if (!$this->isActive) {
            return false;
        }

        $now = $now ?? new DateTime();

        if ($this->startDate && $now < $this->startDate) {
            return false;
        }

        if ($this->endDate && $now > $this->endDate) {
            return false;
        }

        return true;
    }
}
