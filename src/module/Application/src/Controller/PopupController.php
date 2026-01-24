<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\AuthenticationService;
use Application\Service\PopupService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

/**
 * 팝업 컨트롤러
 */
class PopupController extends AbstractActionController
{
    private AuthenticationService $authService;
    private PopupService $popupService;

    public function __construct(
        AuthenticationService $authService,
        PopupService $popupService
    ) {
        $this->authService = $authService;
        $this->popupService = $popupService;
    }

    /**
     * 팝업 목록/생성 (관리자)
     * GET /api/admin/popups - 목록
     * POST /api/admin/popups - 생성
     */
    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            return $this->createAction();
        }

        return $this->listAction();
    }

    /**
     * 현재 활성 팝업 조회 (공개)
     * GET /api/popup/active
     */
    public function activeAction()
    {
        $popup = $this->popupService->getActivePopup();

        if (!$popup) {
            return new JsonModel([
                'success' => true,
                'popup' => null,
            ]);
        }

        return new JsonModel([
            'success' => true,
            'popup' => $popup->toArray(),
        ]);
    }

    /**
     * 팝업 목록 (관리자)
     * GET /api/admin/popups
     */
    public function listAction()
    {
        if (!$this->authService->isAuthenticated()) {
            return new JsonModel([
                'success' => false,
                'message' => '인증이 필요합니다.',
            ]);
        }

        $popups = $this->popupService->getAllPopups();
        $popupsData = [];

        foreach ($popups as $popup) {
            $popupsData[] = $popup->toArray();
        }

        return new JsonModel([
            'success' => true,
            'popups' => $popupsData,
        ]);
    }

    /**
     * 팝업 상세/수정/삭제 (관리자)
     * GET /api/admin/popups/:id - 상세
     * PUT /api/admin/popups/:id - 수정
     * DELETE /api/admin/popups/:id - 삭제
     */
    public function itemAction()
    {
        if ($this->getRequest()->isPut()) {
            return $this->updateAction();
        }

        if ($this->getRequest()->isDelete()) {
            return $this->deleteAction();
        }

        return $this->getAction();
    }

    /**
     * 팝업 상세 (관리자)
     * GET /api/admin/popups/:id
     */
    public function getAction()
    {
        if (!$this->authService->isAuthenticated()) {
            return new JsonModel([
                'success' => false,
                'message' => '인증이 필요합니다.',
            ]);
        }

        $id = (int) $this->params()->fromRoute('id');
        $popup = $this->popupService->getPopupById($id);

        if (!$popup) {
            return new JsonModel([
                'success' => false,
                'message' => '팝업을 찾을 수 없습니다.',
            ]);
        }

        return new JsonModel([
            'success' => true,
            'popup' => $popup->toArray(),
        ]);
    }

    /**
     * 팝업 생성 (관리자)
     * POST /api/admin/popups
     */
    public function createAction()
    {
        if (!$this->authService->canEdit()) {
            return new JsonModel([
                'success' => false,
                'message' => '권한이 없습니다.',
            ]);
        }

        if ($this->getRequest()->isPost()) {
            $data = json_decode($this->getRequest()->getContent(), true);

            if (empty($data['title']) || empty($data['content'])) {
                return new JsonModel([
                    'success' => false,
                    'message' => '제목과 내용은 필수입니다.',
                ]);
            }

            try {
                $popup = $this->popupService->createPopup($data);

                return new JsonModel([
                    'success' => true,
                    'message' => '팝업이 생성되었습니다.',
                    'popup' => $popup->toArray(),
                ]);
            } catch (\Exception $e) {
                return new JsonModel([
                    'success' => false,
                    'message' => '팝업 생성 실패: ' . $e->getMessage(),
                ]);
            }
        }

        return new JsonModel([
            'success' => false,
            'message' => 'POST 메서드만 허용됩니다.',
        ]);
    }

    /**
     * 팝업 수정 (관리자)
     * PUT /api/admin/popups/:id
     */
    public function updateAction()
    {
        if (!$this->authService->canEdit()) {
            return new JsonModel([
                'success' => false,
                'message' => '권한이 없습니다.',
            ]);
        }

        if ($this->getRequest()->isPut()) {
            $id = (int) $this->params()->fromRoute('id');
            $popup = $this->popupService->getPopupById($id);

            if (!$popup) {
                return new JsonModel([
                    'success' => false,
                    'message' => '팝업을 찾을 수 없습니다.',
                ]);
            }

            $data = json_decode($this->getRequest()->getContent(), true);

            try {
                $this->popupService->updatePopup($popup, $data);

                return new JsonModel([
                    'success' => true,
                    'message' => '팝업이 수정되었습니다.',
                    'popup' => $popup->toArray(),
                ]);
            } catch (\Exception $e) {
                return new JsonModel([
                    'success' => false,
                    'message' => '팝업 수정 실패: ' . $e->getMessage(),
                ]);
            }
        }

        return new JsonModel([
            'success' => false,
            'message' => 'PUT 메서드만 허용됩니다.',
        ]);
    }

    /**
     * 팝업 삭제 (관리자)
     * DELETE /api/admin/popups/:id
     */
    public function deleteAction()
    {
        if (!$this->authService->canEdit()) {
            return new JsonModel([
                'success' => false,
                'message' => '권한이 없습니다.',
            ]);
        }

        if ($this->getRequest()->isDelete()) {
            $id = (int) $this->params()->fromRoute('id');
            $popup = $this->popupService->getPopupById($id);

            if (!$popup) {
                return new JsonModel([
                    'success' => false,
                    'message' => '팝업을 찾을 수 없습니다.',
                ]);
            }

            try {
                $this->popupService->deletePopup($popup);

                return new JsonModel([
                    'success' => true,
                    'message' => '팝업이 삭제되었습니다.',
                ]);
            } catch (\Exception $e) {
                return new JsonModel([
                    'success' => false,
                    'message' => '팝업 삭제 실패: ' . $e->getMessage(),
                ]);
            }
        }

        return new JsonModel([
            'success' => false,
            'message' => 'DELETE 메서드만 허용됩니다.',
        ]);
    }

    /**
     * 팝업 활성화/비활성화 토글 (관리자)
     * POST /api/admin/popups/:id/toggle
     */
    public function toggleAction()
    {
        if (!$this->authService->canEdit()) {
            return new JsonModel([
                'success' => false,
                'message' => '권한이 없습니다.',
            ]);
        }

        if ($this->getRequest()->isPost()) {
            $id = (int) $this->params()->fromRoute('id');
            $popup = $this->popupService->getPopupById($id);

            if (!$popup) {
                return new JsonModel([
                    'success' => false,
                    'message' => '팝업을 찾을 수 없습니다.',
                ]);
            }

            try {
                $this->popupService->toggleActive($popup);

                return new JsonModel([
                    'success' => true,
                    'message' => $popup->isActive() ? '팝업이 활성화되었습니다.' : '팝업이 비활성화되었습니다.',
                    'popup' => $popup->toArray(),
                ]);
            } catch (\Exception $e) {
                return new JsonModel([
                    'success' => false,
                    'message' => '팝업 상태 변경 실패: ' . $e->getMessage(),
                ]);
            }
        }

        return new JsonModel([
            'success' => false,
            'message' => 'POST 메서드만 허용됩니다.',
        ]);
    }
}
