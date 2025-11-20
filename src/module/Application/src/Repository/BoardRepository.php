<?php

declare(strict_types=1);

namespace Application\Repository;

use Application\Entity\Board;
use Doctrine\ORM\EntityRepository;

/**
 * 게시판 Repository
 */
class BoardRepository extends EntityRepository
{
    /**
     * boardCode로 게시판 찾기
     */
    public function findByCode(string $boardCode): ?Board
    {
        return $this->findOneBy(['boardCode' => $boardCode]);
    }

    /**
     * 보이는 게시판만 가져오기 (displayOrder 순)
     * @return Board[]
     */
    public function findVisibleBoards(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.isVisible = :visible')
            ->setParameter('visible', true)
            ->orderBy('b.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 게시판 타입별 조회
     * @return Board[]
     */
    public function findByType(string $boardType): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.boardType = :type')
            ->andWhere('b.isVisible = :visible')
            ->setParameter('type', $boardType)
            ->setParameter('visible', true)
            ->orderBy('b.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 모든 게시판 (관리자용)
     * @return Board[]
     */
    public function findAllForAdmin(): array
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.displayOrder', 'ASC')
            ->addOrderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 게시판 이름으로 검색
     * @return Board[]
     */
    public function searchByName(string $keyword): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.boardName LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('b.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 첨부파일 허용 게시판 조회
     * @return Board[]
     */
    public function findAllowingAttachment(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.allowAttachment = :allow')
            ->andWhere('b.isVisible = :visible')
            ->setParameter('allow', true)
            ->setParameter('visible', true)
            ->orderBy('b.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
