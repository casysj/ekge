<?php

declare(strict_types=1);

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTime;

/**
 * 메뉴 구조 엔티티
 */
#[ORM\Entity]
#[ORM\Table(name: 'menus')]
#[ORM\Index(columns: ['parent_id'], name: 'idx_parent_id')]
#[ORM\HasLifecycleCallbacks]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Menu $parent = null;

    /**
     * 하위 메뉴 목록
     * @var Collection<int, Menu>
     */
    #[ORM\OneToMany(targetEntity: Menu::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['displayOrder' => 'ASC'])]
    private Collection $children;

    #[ORM\Column(type: 'string', length: 100, name: 'menuName')]
    private string $menuName;

    #[ORM\Column(type: 'string', length: 20, name: 'menuType')]
    private string $menuType;

    #[ORM\ManyToOne(targetEntity: Board::class, inversedBy: 'menus')]
    #[ORM\JoinColumn(name: 'board_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Board $board = null;

    #[ORM\Column(type: 'string', length: 500, name: 'externalUrl', nullable: true)]
    private ?string $externalUrl = null;

    #[ORM\Column(type: 'integer', name: 'displayOrder', options: ['default' => 0])]
    private int $displayOrder = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $depth = 1;

    #[ORM\Column(type: 'boolean', name: 'isVisible', options: ['default' => true])]
    private bool $isVisible = true;

    #[ORM\Column(type: 'datetime', name: 'createdAt')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', name: 'updatedAt')]
    private DateTime $updatedAt;

    #[ORM\OneToOne(targetEntity: MenuContent::class, mappedBy: 'menu', cascade: ['persist', 'remove'])]
    private ?MenuContent $content = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
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

    public function getParent(): ?Menu
    {
        return $this->parent;
    }

    public function setParent(?Menu $parent): self
    {
        $this->parent = $parent;
        // 깊이 자동 계산
        $this->depth = $parent ? $parent->getDepth() + 1 : 1;
        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Menu $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
        return $this;
    }

    public function removeChild(Menu $child): self
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
        return $this;
    }

    public function getMenuName(): string
    {
        return $this->menuName;
    }

    public function setMenuName(string $menuName): self
    {
        $this->menuName = $menuName;
        return $this;
    }

    public function getMenuType(): string
    {
        return $this->menuType;
    }

    public function setMenuType(string $menuType): self
    {
        $this->menuType = $menuType;
        return $this;
    }

    public function getBoard(): ?Board
    {
        return $this->board;
    }

    public function setBoard(?Board $board): self
    {
        $this->board = $board;
        return $this;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function setExternalUrl(?string $externalUrl): self
    {
        $this->externalUrl = $externalUrl;
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

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): self
    {
        $this->depth = $depth;
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): self
    {
        $this->isVisible = $isVisible;
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

    public function getContent(): ?MenuContent
    {
        return $this->content;
    }

    public function setContent(?MenuContent $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * 루트 메뉴인지 확인
     */
    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    /**
     * 자식 메뉴가 있는지 확인
     */
    public function hasChildren(): bool
    {
        return !$this->children->isEmpty();
    }
}
