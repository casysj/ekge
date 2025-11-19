<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\Board;
use Application\Repository\BoardRepository;
use Doctrine\ORM\EntityManager;

/**
 * 게시판 서비스
 */
class BoardService
{
    private EntityManager $entityManager;
    private BoardRepository $boardRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->boardRepository = $entityManager->getRepository(Board::class);
    }

    /**
     * 게시판 코드로 조회
     */
    public function getBoardByCode(string $boardCode): ?Board
    {
        return $this->boardRepository->findByCode($boardCode);
    }

    /**
     * 보이는 게시판 목록
     * @return Board[]
     */
    public function getVisibleBoards(): array
    {
        return $this->boardRepository->findVisibleBoards();
    }

    /**
     * 게시판 타입별 조회
     * @return Board[]
     */
    public function getBoardsByType(string $type): array
    {
        return $this->boardRepository->findByType($type);
    }

    /**
     * 모든 게시판 (관리자용)
     * @return Board[]
     */
    public function getAllBoards(): array
    {
        return $this->boardRepository->findAllForAdmin();
    }

    /**
     * 게시판 생성
     */
    public function createBoard(array $data): Board
    {
        $board = new Board();
        $board->setBoardCode($data['boardCode'])
              ->setBoardName($data['boardName'])
              ->setBoardType($data['boardType'])
              ->setDescription($data['description'] ?? null)
              ->setDisplayOrder($data['displayOrder'] ?? 0)
              ->setIsVisible($data['isVisible'] ?? true)
              ->setPostsPerPage($data['postsPerPage'] ?? 20)
              ->setAllowAttachment($data['allowAttachment'] ?? true)
              ->setRequireAuth($data['requireAuth'] ?? false);

        $this->entityManager->persist($board);
        $this->entityManager->flush();

        return $board;
    }

    /**
     * 게시판 수정
     */
    public function updateBoard(Board $board, array $data): Board
    {
        if (isset($data['boardName'])) {
            $board->setBoardName($data['boardName']);
        }
        if (isset($data['boardType'])) {
            $board->setBoardType($data['boardType']);
        }
        if (isset($data['description'])) {
            $board->setDescription($data['description']);
        }
        if (isset($data['displayOrder'])) {
            $board->setDisplayOrder($data['displayOrder']);
        }
        if (isset($data['isVisible'])) {
            $board->setIsVisible($data['isVisible']);
        }
        if (isset($data['postsPerPage'])) {
            $board->setPostsPerPage($data['postsPerPage']);
        }
        if (isset($data['allowAttachment'])) {
            $board->setAllowAttachment($data['allowAttachment']);
        }
        if (isset($data['requireAuth'])) {
            $board->setRequireAuth($data['requireAuth']);
        }

        $this->entityManager->flush();

        return $board;
    }

    /**
     * 게시판 삭제
     */
    public function deleteBoard(Board $board): void
    {
        $this->entityManager->remove($board);
        $this->entityManager->flush();
    }

    /**
     * 게시판 표시 순서 변경
     */
    public function updateDisplayOrder(Board $board, int $newOrder): void
    {
        $board->setDisplayOrder($newOrder);
        $this->entityManager->flush();
    }
}
