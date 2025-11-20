<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\BoardService;
use Application\Service\PostService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;

/**
 * 게시판 컨트롤러
 */
class BoardController extends AbstractActionController
{
    private BoardService $boardService;
    private PostService $postService;

    public function __construct(BoardService $boardService, PostService $postService)
    {
        $this->boardService = $boardService;
        $this->postService = $postService;
    }

    /**
     * 게시판 목록
     */
    public function indexAction()
    {
        $boards = $this->boardService->getVisibleBoards();

        return new JsonModel([
            'success' => true,
            'boards' => array_map(function($board) {
                return [
                    'id' => $board->getId(),
                    'code' => $board->getBoardCode(),
                    'name' => $board->getBoardName(),
                    'type' => $board->getBoardType(),
                    'description' => $board->getDescription(),
                    'postCount' => $this->postService->countPostsByBoard($board),
                ];
            }, $boards),
        ]);
    }

    /**
     * 게시판별 게시글 목록
     */
    public function listAction()
    {
        $boardCode = $this->params()->fromRoute('code');
        $page = (int) $this->params()->fromQuery('page', 1);
        $keyword = $this->params()->fromQuery('keyword', '');

        $board = $this->boardService->getBoardByCode($boardCode);

        if (!$board) {
            return new JsonModel([
                'success' => false,
                'error' => '게시판을 찾을 수 없습니다.',
            ]);
        }

        // 검색어가 있으면 검색, 없으면 목록 조회
        if ($keyword) {
            $result = $this->postService->searchPosts($board, $keyword, $page);
        } else {
            $result = $this->postService->getPostsByBoard($board, $page);
        }

        return new JsonModel([
            'success' => true,
            'board' => [
                'id' => $board->getId(),
                'code' => $board->getBoardCode(),
                'name' => $board->getBoardName(),
                'type' => $board->getBoardType(),
            ],
            'posts' => array_map(function($post) {
                return [
                    'id' => $post->getId(),
                    'title' => $post->getTitle(),
                    'authorName' => $post->getAuthorName(),
                    'viewCount' => $post->getViewCount(),
                    'isNotice' => $post->isNotice(),
                    'publishedAt' => $post->getPublishedAt()?->format('Y-m-d H:i:s'),
                    'hasAttachments' => count($post->getAttachments()) > 0,
                ];
            }, $result['posts']),
            'notices' => isset($result['notices']) ? array_map(function($post) {
                return [
                    'id' => $post->getId(),
                    'title' => $post->getTitle(),
                    'authorName' => $post->getAuthorName(),
                    'viewCount' => $post->getViewCount(),
                    'publishedAt' => $post->getPublishedAt()?->format('Y-m-d H:i:s'),
                ];
            }, $result['notices']) : [],
            'pagination' => [
                'total' => $result['total'],
                'pages' => $result['pages'],
                'currentPage' => $result['currentPage'],
                'perPage' => $result['perPage'],
            ],
        ]);
    }

    /**
     * 게시글 상세
     */
    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id');

        $post = $this->postService->getPostById($id, true); // 조회수 증가

        if (!$post || !$post->isPublished()) {
            return new JsonModel([
                'success' => false,
                'error' => '게시글을 찾을 수 없습니다.',
            ]);
        }

        $board = $post->getBoard();
        $adjacent = $this->postService->getAdjacentPosts($post);

        return new JsonModel([
            'success' => true,
            'post' => [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'authorName' => $post->getAuthorName(),
                'viewCount' => $post->getViewCount(),
                'isNotice' => $post->isNotice(),
                'publishedAt' => $post->getPublishedAt()?->format('Y-m-d H:i:s'),
                'board' => [
                    'id' => $board->getId(),
                    'code' => $board->getBoardCode(),
                    'name' => $board->getBoardName(),
                ],
                'attachments' => array_map(function($attachment) {
                    return [
                        'id' => $attachment->getId(),
                        'originalName' => $attachment->getOriginalName(),
                        'fileSize' => $attachment->getFileSize(),
                        'fileType' => $attachment->getFileType(),
                        'downloadCount' => $attachment->getDownloadCount(),
                    ];
                }, $post->getAttachments()->toArray()),
            ],
            'adjacent' => [
                'prev' => $adjacent['prev'] ? [
                    'id' => $adjacent['prev']->getId(),
                    'title' => $adjacent['prev']->getTitle(),
                ] : null,
                'next' => $adjacent['next'] ? [
                    'id' => $adjacent['next']->getId(),
                    'title' => $adjacent['next']->getTitle(),
                ] : null,
            ],
        ]);
    }
}
