<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\AuthenticationService;
use Application\Service\BoardService;
use Application\Service\PostService;
use Application\Service\FileUploadService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

/**
 * 관리자 컨트롤러
 */
class AdminController extends AbstractActionController
{
    private AuthenticationService $authService;
    private BoardService $boardService;
    private PostService $postService;
    private FileUploadService $fileUploadService;

    public function __construct(
        AuthenticationService $authService,
        BoardService $boardService,
        PostService $postService,
        FileUploadService $fileUploadService
    ) {
        $this->authService = $authService;
        $this->boardService = $boardService;
        $this->postService = $postService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * 로그인
     * POST /api/admin/login
     */
    public function loginAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = json_decode($this->getRequest()->getContent(), true);

            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';

            $result = $this->authService->login($username, $password);

            if ($result['success']) {
                return new JsonModel([
                    'success' => true,
                    'message' => $result['message'],
                    'user' => [
                        'id' => $result['user']->getId(),
                        'username' => $result['user']->getUsername(),
                        'displayName' => $result['user']->getDisplayName(),
                        'role' => $result['user']->getRole(),
                    ],
                ]);
            }

            return new JsonModel([
                'success' => false,
                'message' => $result['message'],
            ]);
        }

        return new JsonModel([
            'success' => false,
            'message' => 'POST 메서드만 허용됩니다.',
        ]);
    }

    /**
     * 로그아웃
     * POST /api/admin/logout
     */
    public function logoutAction()
    {
        $this->authService->logout();

        return new JsonModel([
            'success' => true,
            'message' => '로그아웃되었습니다.',
        ]);
    }

    /**
     * 현재 사용자 정보
     * GET /api/admin/me
     */
    public function meAction()
    {
        if (!$this->authService->isAuthenticated()) {
            return new JsonModel([
                'success' => false,
                'message' => '인증이 필요합니다.',
            ]);
        }

        $user = $this->authService->getCurrentUser();

        return new JsonModel([
            'success' => true,
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'displayName' => $user->getDisplayName(),
                'role' => $user->getRole(),
                'isActive' => $user->isActive(),
            ],
        ]);
    }

    /**
     * 게시글 작성
     * POST /api/admin/posts
     */
    public function createPostAction()
    {
        if (!$this->authService->canEdit()) {
            return new JsonModel([
                'success' => false,
                'message' => '권한이 없습니다.',
            ]);
        }

        if ($this->getRequest()->isPost()) {
            $data = json_decode($this->getRequest()->getContent(), true);

            // boardCode 또는 board_id로 게시판 찾기
            if (isset($data['boardCode'])) {
                $board = $this->boardService->getBoardByCode($data['boardCode']);
            } elseif (isset($data['board_id'])) {
                $board = $this->boardService->getBoardById((int) $data['board_id']);
            } else {
                return new JsonModel([
                    'success' => false,
                    'message' => '게시판 정보가 필요합니다.',
                ]);
            }

            if (!$board) {
                return new JsonModel([
                    'success' => false,
                    'message' => '게시판을 찾을 수 없습니다.',
                ]);
            }

            try {
                $user = $this->authService->getCurrentUser();
                $post = $this->postService->createPost($board, $data, $user);

                return new JsonModel([
                    'success' => true,
                    'message' => '게시글이 작성되었습니다.',
                    'post' => [
                        'id' => $post->getId(),
                        'title' => $post->getTitle(),
                    ],
                ]);
            } catch (\Exception $e) {
                return new JsonModel([
                    'success' => false,
                    'message' => '게시글 작성 실패: ' . $e->getMessage(),
                ]);
            }
        }

        return new JsonModel([
            'success' => false,
            'message' => 'POST 메서드만 허용됩니다.',
        ]);
    }

    /**
     * 게시글 수정
     * PUT /api/admin/posts/:id
     */
    public function updatePostAction()
    {
        if (!$this->authService->canEdit()) {
            return new JsonModel([
                'success' => false,
                'message' => '권한이 없습니다.',
            ]);
        }

        if ($this->getRequest()->isPut()) {
            $id = (int) $this->params()->fromRoute('id');
            $post = $this->postService->getPostById($id);

            if (!$post) {
                return new JsonModel([
                    'success' => false,
                    'message' => '게시글을 찾을 수 없습니다.',
                ]);
            }

            $data = json_decode($this->getRequest()->getContent(), true);

            try {
                $this->postService->updatePost($post, $data);

                return new JsonModel([
                    'success' => true,
                    'message' => '게시글이 수정되었습니다.',
                ]);
            } catch (\Exception $e) {
                return new JsonModel([
                    'success' => false,
                    'message' => '게시글 수정 실패: ' . $e->getMessage(),
                ]);
            }
        }

        return new JsonModel([
            'success' => false,
            'message' => 'PUT 메서드만 허용됩니다.',
        ]);
    }

    /**
     * 게시글 삭제
     * DELETE /api/admin/posts/:id
     */
    public function deletePostAction()
    {
        if (!$this->authService->canEdit()) {
            return new JsonModel([
                'success' => false,
                'message' => '권한이 없습니다.',
            ]);
        }

        if ($this->getRequest()->isDelete()) {
            $id = (int) $this->params()->fromRoute('id');
            $post = $this->postService->getPostById($id);

            if (!$post) {
                return new JsonModel([
                    'success' => false,
                    'message' => '게시글을 찾을 수 없습니다.',
                ]);
            }

            try {
                $this->postService->deletePost($post);

                return new JsonModel([
                    'success' => true,
                    'message' => '게시글이 삭제되었습니다.',
                ]);
            } catch (\Exception $e) {
                return new JsonModel([
                    'success' => false,
                    'message' => '게시글 삭제 실패: ' . $e->getMessage(),
                ]);
            }
        }

        return new JsonModel([
            'success' => false,
            'message' => 'DELETE 메서드만 허용됩니다.',
        ]);
    }

    /**
     * 파일 업로드
     * POST /api/admin/upload
     */
    public function uploadAction()
    {
        if (!$this->authService->canEdit()) {
            return new JsonModel([
                'success' => false,
                'message' => '권한이 없습니다.',
            ]);
        }

        if ($this->getRequest()->isPost()) {
            $files = $this->getRequest()->getFiles()->toArray();
            $postId = (int) $this->params()->fromPost('postId');

            if (!$postId) {
                return new JsonModel([
                    'success' => false,
                    'message' => '게시글 ID가 필요합니다.',
                ]);
            }

            $post = $this->postService->getPostById($postId);

            if (!$post) {
                return new JsonModel([
                    'success' => false,
                    'message' => '게시글을 찾을 수 없습니다.',
                ]);
            }

            try {
                $uploadedFiles = [];

                foreach ($files as $file) {
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $attachment = $this->fileUploadService->uploadFile($file, $post);
                        $uploadedFiles[] = [
                            'id' => $attachment->getId(),
                            'originalName' => $attachment->getOriginalName(),
                            'fileSize' => $attachment->getFileSize(),
                            'fileType' => $attachment->getFileType(),
                        ];
                    }
                }

                return new JsonModel([
                    'success' => true,
                    'message' => count($uploadedFiles) . '개 파일이 업로드되었습니다.',
                    'files' => $uploadedFiles,
                ]);
            } catch (\Exception $e) {
                return new JsonModel([
                    'success' => false,
                    'message' => '파일 업로드 실패: ' . $e->getMessage(),
                ]);
            }
        }

        return new JsonModel([
            'success' => false,
            'message' => 'POST 메서드만 허용됩니다.',
        ]);
    }

    /**
     * 대시보드 통계
     * GET /api/admin/stats
     */
    public function statsAction()
    {
        if (!$this->authService->isAuthenticated()) {
            return new JsonModel([
                'success' => false,
                'message' => '인증이 필요합니다.',
            ]);
        }

        $boards = $this->boardService->getVisibleBoards();
        $stats = [];

        foreach ($boards as $board) {
            $stats[] = [
                'boardCode' => $board->getBoardCode(),
                'boardName' => $board->getBoardName(),
                'postCount' => $this->postService->countPostsByBoard($board),
            ];
        }

        return new JsonModel([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
