<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\MenuService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

/**
 * 메뉴 컨트롤러
 */
class MenuController extends AbstractActionController
{
    private MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * 네비게이션 메뉴 (프론트엔드용)
     * GET /api/menus
     */
    public function indexAction()
    {
        $navigation = $this->menuService->buildNavigationData();

        return new JsonModel([
            'success' => true,
            'menus' => $navigation,
        ]);
    }

    /**
     * 메뉴 상세 (HTML 컨텐츠 포함)
     * GET /api/menus/:id
     */
    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id');
        $menu = $this->menuService->getMenuById($id);

        if (!$menu || !$menu->isVisible()) {
            return new JsonModel([
                'success' => false,
                'error' => '메뉴를 찾을 수 없습니다.',
            ]);
        }

        $data = [
            'id' => $menu->getId(),
            'name' => $menu->getMenuName(),
            'type' => $menu->getMenuType(),
            'depth' => $menu->getDepth(),
        ];

        // 타입별 추가 정보
        if ($menu->getMenuType() === 'board' && $menu->getBoard()) {
            $data['board'] = [
                'id' => $menu->getBoard()->getId(),
                'code' => $menu->getBoard()->getBoardCode(),
                'name' => $menu->getBoard()->getBoardName(),
            ];
        } elseif ($menu->getMenuType() === 'external') {
            $data['url'] => $menu->getExternalUrl();
        } elseif ($menu->getMenuType() === 'html' && $menu->getContent()) {
            $data['content'] = $menu->getContent()->getContent();
        }

        // 경로 (breadcrumb)
        $path = $this->menuService->getMenuPath($menu);
        $data['path'] = array_map(function($m) {
            return [
                'id' => $m->getId(),
                'name' => $m->getMenuName(),
            ];
        }, $path);

        return new JsonModel([
            'success' => true,
            'menu' => $data,
        ]);
    }
}
