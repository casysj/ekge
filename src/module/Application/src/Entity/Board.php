<?php

declare(strict_types=1);

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTime;

/**
 * 게시판 종류 엔티티
 */
#[ORM\Entity(repositoryClass: \Application\Repository\BoardRepository::class)]
#[ORM\Table(name: 'boards')]
#[ORM\HasLifecycleCallbacks]
class Board
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true, name: 'boardCode')]
    private string $boardCode;

    #[ORM\Column(type: 'string', length: 100, name: 'boardName')]
    private string $boardName;

    #[ORM\Column(type: 'string', length: 20, name: 'boardType')]
    private string $boardType;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', name: 'displayOrder', options: ['default' => 0])]
    private int $displayOrder = 0;

    #[ORM\Column(type: 'boolean', name: 'isVisible', options: ['default' => true])]
    private bool $isVisible = true;

    #[ORM\Column(type: 'integer', name: 'postsPerPage', options: ['default' => 20])]
    private int $postsPerPage = 20;

    #[ORM\Column(type: 'boolean', name: 'allowAttachment', options: ['default' => true])]
    private bool $allowAttachment = true;

    #[ORM\Column(type: 'boolean', name: 'requireAuth', options: ['default' => false])]
    private bool $requireAuth = false;

    #[ORM\Column(type: 'datetime', name: 'createdAt')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', name: 'updatedAt')]
    private DateTime $updatedAt;

    /**
     * 이 게시판의 게시글 목록
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'board')]
    private Collection $posts;

    /**
     * 이 게시판을 참조하는 메뉴 목록
     * @var Collection<int, Menu>
     */
    #[ORM\OneToMany(targetEntity: Menu::class, mappedBy: 'board')]
    private Collection $menus;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->menus = new ArrayCollection();
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

    public function getBoardCode(): string
    {
        return $this->boardCode;
    }

    public function setBoardCode(string $boardCode): self
    {
        $this->boardCode = $boardCode;
        return $this;
    }

    public function getBoardName(): string
    {
        return $this->boardName;
    }

    public function setBoardName(string $boardName): self
    {
        $this->boardName = $boardName;
        return $this;
    }

    public function getBoardType(): string
    {
        return $this->boardType;
    }

    public function setBoardType(string $boardType): self
    {
        $this->boardType = $boardType;
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

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): self
    {
        $this->displayOrder = $displayOrder;
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

    public function getPostsPerPage(): int
    {
        return $this->postsPerPage;
    }

    public function setPostsPerPage(int $postsPerPage): self
    {
        $this->postsPerPage = $postsPerPage;
        return $this;
    }

    public function allowAttachment(): bool
    {
        return $this->allowAttachment;
    }

    public function setAllowAttachment(bool $allowAttachment): self
    {
        $this->allowAttachment = $allowAttachment;
        return $this;
    }

    public function requireAuth(): bool
    {
        return $this->requireAuth;
    }

    public function setRequireAuth(bool $requireAuth): self
    {
        $this->requireAuth = $requireAuth;
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
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setBoard($this);
        }
        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getBoard() === $this) {
                $post->setBoard(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->setBoard($this);
        }
        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->removeElement($menu)) {
            if ($menu->getBoard() === $this) {
                $menu->setBoard(null);
            }
        }
        return $this;
    }
}
