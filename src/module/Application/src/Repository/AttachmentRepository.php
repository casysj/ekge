<?php

declare(strict_types=1);

namespace Application\Repository;

use Application\Entity\Attachment;
use Application\Entity\Post;
use Doctrine\ORM\EntityRepository;

/**
 * 첨부파일 Repository
 */
class AttachmentRepository extends EntityRepository
{
    /**
     * 게시글의 첨부파일 조회
     * @return Attachment[]
     */
    public function findByPost(Post $post): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.post = :post')
            ->setParameter('post', $post)
            ->orderBy('a.displayOrder', 'ASC')
            ->addOrderBy('a.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 게시글의 이미지 파일만 조회
     * @return Attachment[]
     */
    public function findImagesByPost(Post $post): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.post = :post')
            ->andWhere('a.fileType = :type')
            ->setParameter('post', $post)
            ->setParameter('type', 'image')
            ->orderBy('a.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 파일 타입별 조회
     * @return Attachment[]
     */
    public function findByFileType(string $fileType, int $limit = 100): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.fileType = :type')
            ->setParameter('type', $fileType)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 전체 파일 크기 합계
     */
    public function getTotalFileSize(): int
    {
        $result = $this->createQueryBuilder('a')
            ->select('SUM(a.fileSize)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * 다운로드 수 증가
     */
    public function incrementDownloadCount(Attachment $attachment): void
    {
        $attachment->incrementDownloadCount();
        $this->getEntityManager()->flush();
    }

    /**
     * 인기 다운로드 파일
     * @return Attachment[]
     */
    public function findMostDownloaded(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.downloadCount', 'DESC')
            ->addOrderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
