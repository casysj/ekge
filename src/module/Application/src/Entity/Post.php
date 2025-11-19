<?php

declare(strict_types=1);

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTime;

/**
 * 게시글 엔티티
 */
#[ORM\Entity]
#[ORM\Table(name: 'posts')]
#[ORM\Index(columns: ['board_id', 'isNotice', 'isPublished', 'publishedAt', 'createdAt'], name: 'idx_board_publish')]
#[ORM\Index(columns: ['title', 'content'], name: 'idx_fulltext', flags: ['fulltext'])]
#[ORM\HasLifecycleCallbacks]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Board::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(name: 'board_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Board $board = null;

    #[ORM\Column(type: 'string', length: 200)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'string', length: 100, name: 'authorName')]
    private string $authorName;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(type: 'integer', name: 'viewCount', options: ['default' => 0, 'unsigned' => true])]
    private int $viewCount = 0;

    #[ORM\Column(type: 'boolean', name: 'isNotice', options: ['default' => false])]
    private bool $isNotice = false;

    #[ORM\Column(type: 'boolean', name: 'isPublished', options: ['default' => true])]
    private bool $isPublished = true;

    #[ORM\Column(type: 'datetime', name: 'publishedAt', nullable: true)]
    private ?DateTime $publishedAt = null;

    #[ORM\Column(type: 'datetime', name: 'createdAt')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', name: 'updatedAt')]
    private DateTime $updatedAt;

    /**
     * 첨부파일 목록
     * @var Collection<int, Attachment>
     */
    #[ORM\OneToMany(targetEntity: Attachment::class, mappedBy: 'post', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['displayOrder' => 'ASC'])]
    private Collection $attachments;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTime();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if ($this->isPublished && $this->publishedAt === null) {
            $this->publishedAt = new DateTime();
        }
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function setViewCount(int $viewCount): self
    {
        $this->viewCount = $viewCount;
        return $this;
    }

    public function incrementViewCount(): self
    {
        $this->viewCount++;
        return $this;
    }

    public function isNotice(): bool
    {
        return $this->isNotice;
    }

    public function setIsNotice(bool $isNotice): self
    {
        $this->isNotice = $isNotice;
        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;
        if ($isPublished && $this->publishedAt === null) {
            $this->publishedAt = new DateTime();
        }
        return $this;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;
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
     * @return Collection<int, Attachment>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(Attachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setPost($this);
        }
        return $this;
    }

    public function removeAttachment(Attachment $attachment): self
    {
        if ($this->attachments->removeElement($attachment)) {
            if ($attachment->getPost() === $this) {
                $attachment->setPost(null);
            }
        }
        return $this;
    }

    /**
     * 이미지 첨부파일만 가져오기
     * @return Attachment[]
     */
    public function getImageAttachments(): array
    {
        return $this->attachments->filter(
            fn(Attachment $attachment) => $attachment->getFileType() === 'image'
        )->toArray();
    }
}
