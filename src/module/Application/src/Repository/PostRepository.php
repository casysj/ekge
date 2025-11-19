<?php

declare(strict_types=1);

namespace Application\Repository;

use Application\Entity\Post;
use Application\Entity\Board;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * 게시글 Repository
 */
class PostRepository extends EntityRepository
{
    /**
     * 게시판별 게시글 목록 (페이징, 공지사항 분리)
     *
     * @param Board $board 게시판
     * @param int $page 페이지 번호 (1부터 시작)
     * @param int $perPage 페이지당 게시글 수
     * @return array ['posts' => Post[], 'notices' => Post[], 'total' => int, 'pages' => int]
     */
    public function findByBoardWithPagination(Board $board, int $page = 1, int $perPage = 20): array
    {
        // 공지사항 조회
        $notices = $this->createQueryBuilder('p')
            ->where('p.board = :board')
            ->andWhere('p.isPublished = :published')
            ->andWhere('p.isNotice = :notice')
            ->setParameter('board', $board)
            ->setParameter('published', true)
            ->setParameter('notice', true)
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();

        // 일반 게시글 조회 (페이징)
        $qb = $this->createQueryBuilder('p')
            ->where('p.board = :board')
            ->andWhere('p.isPublished = :published')
            ->andWhere('p.isNotice = :notice')
            ->setParameter('board', $board)
            ->setParameter('published', true)
            ->setParameter('notice', false)
            ->orderBy('p.publishedAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $paginator = new Paginator($qb);
        $total = count($paginator);
        $pages = (int) ceil($total / $perPage);

        return [
            'posts' => iterator_to_array($paginator),
            'notices' => $notices,
            'total' => $total,
            'pages' => $pages,
            'currentPage' => $page,
            'perPage' => $perPage,
        ];
    }

    /**
     * 게시글 ID로 조회 (조회수 증가 포함)
     */
    public function findByIdWithViewCount(int $id): ?Post
    {
        $post = $this->find($id);

        if ($post && $post->isPublished()) {
            $post->incrementViewCount();
            $this->getEntityManager()->flush();
        }

        return $post;
    }

    /**
     * 게시글 검색 (제목 + 내용)
     *
     * @param Board $board 게시판
     * @param string $keyword 검색어
     * @param int $page 페이지 번호
     * @param int $perPage 페이지당 게시글 수
     * @return array ['posts' => Post[], 'total' => int, 'pages' => int]
     */
    public function searchInBoard(Board $board, string $keyword, int $page = 1, int $perPage = 20): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.board = :board')
            ->andWhere('p.isPublished = :published')
            ->andWhere('(p.title LIKE :keyword OR p.content LIKE :keyword OR p.authorName LIKE :keyword)')
            ->setParameter('board', $board)
            ->setParameter('published', true)
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('p.publishedAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $paginator = new Paginator($qb);
        $total = count($paginator);
        $pages = (int) ceil($total / $perPage);

        return [
            'posts' => iterator_to_array($paginator),
            'total' => $total,
            'pages' => $pages,
            'currentPage' => $page,
            'perPage' => $perPage,
            'keyword' => $keyword,
        ];
    }

    /**
     * 최근 게시글 가져오기
     *
     * @param int $limit 개수
     * @param Board|null $board 특정 게시판 (null이면 전체)
     * @return Post[]
     */
    public function findRecent(int $limit = 5, ?Board $board = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.isPublished = :published')
            ->setParameter('published', true);

        if ($board) {
            $qb->andWhere('p.board = :board')
               ->setParameter('board', $board);
        }

        return $qb->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 인기 게시글 (조회수 기준)
     *
     * @param int $limit 개수
     * @param Board|null $board 특정 게시판 (null이면 전체)
     * @return Post[]
     */
    public function findPopular(int $limit = 5, ?Board $board = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.isPublished = :published')
            ->setParameter('published', true);

        if ($board) {
            $qb->andWhere('p.board = :board')
               ->setParameter('board', $board);
        }

        return $qb->orderBy('p.viewCount', 'DESC')
            ->addOrderBy('p.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 이전/다음 게시글 찾기
     *
     * @return array ['prev' => Post|null, 'next' => Post|null]
     */
    public function findAdjacentPosts(Post $post): array
    {
        $board = $post->getBoard();

        // 이전 글 (더 오래된)
        $prev = $this->createQueryBuilder('p')
            ->where('p.board = :board')
            ->andWhere('p.isPublished = :published')
            ->andWhere('p.publishedAt < :publishedAt')
            ->setParameter('board', $board)
            ->setParameter('published', true)
            ->setParameter('publishedAt', $post->getPublishedAt())
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // 다음 글 (더 최근)
        $next = $this->createQueryBuilder('p')
            ->where('p.board = :board')
            ->andWhere('p.isPublished = :published')
            ->andWhere('p.publishedAt > :publishedAt')
            ->setParameter('board', $board)
            ->setParameter('published', true)
            ->setParameter('publishedAt', $post->getPublishedAt())
            ->orderBy('p.publishedAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return [
            'prev' => $prev,
            'next' => $next,
        ];
    }

    /**
     * 게시판별 게시글 수 카운트
     */
    public function countByBoard(Board $board, bool $publishedOnly = true): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.board = :board')
            ->setParameter('board', $board);

        if ($publishedOnly) {
            $qb->andWhere('p.isPublished = :published')
               ->setParameter('published', true);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
