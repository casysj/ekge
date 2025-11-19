<?php

declare(strict_types=1);

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * 첨부파일 엔티티
 */
#[ORM\Entity]
#[ORM\Table(name: 'attachments')]
#[ORM\Index(columns: ['post_id'], name: 'idx_post_id')]
class Attachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'attachments')]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Post $post = null;

    #[ORM\Column(type: 'string', length: 255, name: 'originalName')]
    private string $originalName;

    #[ORM\Column(type: 'string', length: 255, name: 'savedName')]
    private string $savedName;

    #[ORM\Column(type: 'string', length: 500, name: 'filePath')]
    private string $filePath;

    #[ORM\Column(type: 'bigint', name: 'fileSize', options: ['unsigned' => true])]
    private int $fileSize;

    #[ORM\Column(type: 'string', length: 100, name: 'mimeType')]
    private string $mimeType;

    #[ORM\Column(type: 'string', length: 20, name: 'fileType')]
    private string $fileType;

    #[ORM\Column(type: 'integer', name: 'imageWidth', nullable: true, options: ['unsigned' => true])]
    private ?int $imageWidth = null;

    #[ORM\Column(type: 'integer', name: 'imageHeight', nullable: true, options: ['unsigned' => true])]
    private ?int $imageHeight = null;

    #[ORM\Column(type: 'integer', name: 'downloadCount', options: ['default' => 0, 'unsigned' => true])]
    private int $downloadCount = 0;

    #[ORM\Column(type: 'integer', name: 'displayOrder', options: ['default' => 0])]
    private int $displayOrder = 0;

    #[ORM\Column(type: 'datetime', name: 'createdAt')]
    private DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;
        return $this;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;
        return $this;
    }

    public function getSavedName(): string
    {
        return $this->savedName;
    }

    public function setSavedName(string $savedName): self
    {
        $this->savedName = $savedName;
        return $this;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): self
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getFileType(): string
    {
        return $this->fileType;
    }

    public function setFileType(string $fileType): self
    {
        $this->fileType = $fileType;
        return $this;
    }

    public function getImageWidth(): ?int
    {
        return $this->imageWidth;
    }

    public function setImageWidth(?int $imageWidth): self
    {
        $this->imageWidth = $imageWidth;
        return $this;
    }

    public function getImageHeight(): ?int
    {
        return $this->imageHeight;
    }

    public function setImageHeight(?int $imageHeight): self
    {
        $this->imageHeight = $imageHeight;
        return $this;
    }

    public function getDownloadCount(): int
    {
        return $this->downloadCount;
    }

    public function setDownloadCount(int $downloadCount): self
    {
        $this->downloadCount = $downloadCount;
        return $this;
    }

    public function incrementDownloadCount(): self
    {
        $this->downloadCount++;
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

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * 파일이 이미지인지 확인
     */
    public function isImage(): bool
    {
        return $this->fileType === 'image';
    }

    /**
     * 파일 크기를 사람이 읽기 쉬운 형식으로 반환
     */
    public function getHumanReadableSize(): string
    {
        $bytes = $this->fileSize;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }
}
