<?php

declare(strict_types=1);

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * 사이트 설정 엔티티
 */
#[ORM\Entity]
#[ORM\Table(name: 'settings')]
#[ORM\HasLifecycleCallbacks]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true, name: 'settingKey')]
    private string $settingKey;

    #[ORM\Column(type: 'text', name: 'settingValue')]
    private string $settingValue;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime', name: 'updatedAt')]
    private DateTime $updatedAt;

    public function __construct()
    {
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

    public function getSettingKey(): string
    {
        return $this->settingKey;
    }

    public function setSettingKey(string $settingKey): self
    {
        $this->settingKey = $settingKey;
        return $this;
    }

    public function getSettingValue(): string
    {
        return $this->settingValue;
    }

    public function setSettingValue(string $settingValue): self
    {
        $this->settingValue = $settingValue;
        $this->updatedAt = new DateTime();
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

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * 값을 배열로 파싱 (JSON 형식인 경우)
     */
    public function getValueAsArray(): ?array
    {
        $decoded = json_decode($this->settingValue, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * 값을 boolean으로 파싱
     */
    public function getValueAsBoolean(): bool
    {
        return in_array(strtolower($this->settingValue), ['true', '1', 'yes', 'on']);
    }

    /**
     * 값을 정수로 파싱
     */
    public function getValueAsInt(): int
    {
        return (int) $this->settingValue;
    }
}
