<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\Attachment;
use Application\Entity\Post;
use Application\Repository\AttachmentRepository;
use Doctrine\ORM\EntityManager;

/**
 * 첨부파일 서비스
 */
class AttachmentService
{
    private EntityManager $entityManager;
    private AttachmentRepository $attachmentRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->attachmentRepository = $entityManager->getRepository(Attachment::class);
    }

    /**
     * 게시글의 첨부파일 조회
     * @return Attachment[]
     */
    public function getAttachmentsByPost(Post $post): array
    {
        return $this->attachmentRepository->findByPost($post);
    }

    /**
     * 게시글의 이미지만 조회
     * @return Attachment[]
     */
    public function getImagesByPost(Post $post): array
    {
        return $this->attachmentRepository->findImagesByPost($post);
    }

    /**
     * 첨부파일 ID로 조회
     */
    public function getAttachmentById(int $id): ?Attachment
    {
        return $this->attachmentRepository->find($id);
    }

    /**
     * 다운로드 수 증가
     */
    public function incrementDownloadCount(Attachment $attachment): void
    {
        $this->attachmentRepository->incrementDownloadCount($attachment);
    }

    /**
     * 파일 타입별 조회
     * @return Attachment[]
     */
    public function getAttachmentsByType(string $fileType, int $limit = 100): array
    {
        return $this->attachmentRepository->findByFileType($fileType, $limit);
    }

    /**
     * 전체 파일 크기
     */
    public function getTotalFileSize(): int
    {
        return $this->attachmentRepository->getTotalFileSize();
    }

    /**
     * 인기 다운로드 파일
     * @return Attachment[]
     */
    public function getMostDownloadedFiles(int $limit = 10): array
    {
        return $this->attachmentRepository->findMostDownloaded($limit);
    }

    /**
     * 첨부파일 표시 순서 변경
     */
    public function updateDisplayOrder(Attachment $attachment, int $newOrder): void
    {
        $attachment->setDisplayOrder($newOrder);
        $this->entityManager->flush();
    }
}
