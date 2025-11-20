<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\Post;
use Application\Entity\Board;
use Application\Entity\User;
use Application\Repository\PostRepository;
use Doctrine\ORM\EntityManager;
use DateTime;

/**
 * 게시글 서비스
 */
class PostService
{
    private EntityManager $entityManager;
    private PostRepository $postRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->postRepository = $entityManager->getRepository(Post::class);
    }

    /**
     * 게시글 ID로 조회
     */
    public function getPostById(int $id, bool $incrementView = false): ?Post
    {
        if ($incrementView) {
            return $this->postRepository->findByIdWithViewCount($id);
        }

        return $this->postRepository->find($id);
    }

    /**
     * 게시판별 게시글 목록 (페이징)
     */
    public function getPostsByBoard(Board $board, int $page = 1, ?int $perPage = null): array
    {
        $perPage = $perPage ?? $board->getPostsPerPage();
        return $this->postRepository->findByBoardWithPagination($board, $page, $perPage);
    }

    /**
     * 게시글 검색
     */
    public function searchPosts(Board $board, string $keyword, int $page = 1, ?int $perPage = null): array
    {
        $perPage = $perPage ?? $board->getPostsPerPage();
        return $this->postRepository->searchInBoard($board, $keyword, $page, $perPage);
    }

    /**
     * 최근 게시글
     * @return Post[]
     */
    public function getRecentPosts(int $limit = 5, ?Board $board = null): array
    {
        return $this->postRepository->findRecent($limit, $board);
    }

    /**
     * 인기 게시글
     * @return Post[]
     */
    public function getPopularPosts(int $limit = 5, ?Board $board = null): array
    {
        return $this->postRepository->findPopular($limit, $board);
    }

    /**
     * 이전/다음 게시글
     */
    public function getAdjacentPosts(Post $post): array
    {
        return $this->postRepository->findAdjacentPosts($post);
    }

    /**
     * 게시글 생성
     */
    public function createPost(Board $board, array $data, ?User $user = null): Post
    {
        $post = new Post();
        $post->setBoard($board)
             ->setTitle($data['title'])
             ->setContent($data['content'])
             ->setAuthorName($data['authorName'] ?? '관리자')
             ->setUser($user)
             ->setIsNotice($data['isNotice'] ?? false)
             ->setIsPublished($data['isPublished'] ?? true);

        if (isset($data['publishedAt'])) {
            $post->setPublishedAt(new DateTime($data['publishedAt']));
        }

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $post;
    }

    /**
     * 게시글 수정
     */
    public function updatePost(Post $post, array $data): Post
    {
        if (isset($data['title'])) {
            $post->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $post->setContent($data['content']);
        }
        if (isset($data['authorName'])) {
            $post->setAuthorName($data['authorName']);
        }
        if (isset($data['isNotice'])) {
            $post->setIsNotice($data['isNotice']);
        }
        if (isset($data['isPublished'])) {
            $post->setIsPublished($data['isPublished']);
        }
        if (isset($data['publishedAt'])) {
            $post->setPublishedAt(new DateTime($data['publishedAt']));
        }

        $this->entityManager->flush();

        return $post;
    }

    /**
     * 게시글 삭제
     */
    public function deletePost(Post $post): void
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    /**
     * 게시글 수 카운트
     */
    public function countPostsByBoard(Board $board, bool $publishedOnly = true): int
    {
        return $this->postRepository->countByBoard($board, $publishedOnly);
    }

    /**
     * 게시글 발행/미발행 토글
     */
    public function togglePublish(Post $post): Post
    {
        $post->setIsPublished(!$post->isPublished());
        $this->entityManager->flush();

        return $post;
    }

    /**
     * 공지사항 설정/해제
     */
    public function toggleNotice(Post $post): Post
    {
        $post->setIsNotice(!$post->isNotice());
        $this->entityManager->flush();

        return $post;
    }
}
