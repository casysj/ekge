<?php

declare(strict_types=1);

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * 메뉴 HTML 컨텐츠 엔티티
 */
#[ORM\Entity]
#[ORM\Table(name: 'menuContents')]
#[ORM\HasLifecycleCallbacks]
class MenuContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Menu::class, inversedBy: 'content')]
    #[ORM\JoinColumn(name: 'menu_id', referencedColumnName: 'id', unique: true, nullable: false, onDelete: 'CASCADE')]
    private ?Menu $menu = null;

    #[ORM\Column(type: 'text')]
    private string $content;

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

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }
}
