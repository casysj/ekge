<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\Menu;
use Application\Entity\MenuContent;
use Application\Entity\Board;
use Application\Repository\MenuRepository;
use Doctrine\ORM\EntityManager;

/**
 * 메뉴 서비스
 */
class MenuService
{
    private EntityManager $entityManager;
    private MenuRepository $menuRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->menuRepository = $entityManager->getRepository(Menu::class);
    }

    /**
     * 루트 메뉴 조회
     * @return Menu[]
     */
    public function getRootMenus(): array
    {
        return $this->menuRepository->findRootMenus();
    }

    /**
     * 전체 메뉴 트리
     * @return Menu[]
     */
    public function getMenuTree(): array
    {
        return $this->menuRepository->findMenuTree();
    }

    /**
     * 자식 메뉴 조회
     * @return Menu[]
     */
    public function getChildren(Menu $parent): array
    {
        return $this->menuRepository->findChildren($parent);
    }

    /**
     * depth별 메뉴 조회
     * @return Menu[]
     */
    public function getMenusByDepth(int $depth): array
    {
        return $this->menuRepository->findByDepth($depth);
    }

    /**
     * 메뉴 타입별 조회
     * @return Menu[]
     */
    public function getMenusByType(string $menuType): array
    {
        return $this->menuRepository->findByType($menuType);
    }

    /**
     * 모든 메뉴 (관리자용)
     * @return Menu[]
     */
    public function getAllMenus(): array
    {
        return $this->menuRepository->findAllForAdmin();
    }

    /**
     * 메뉴 경로 가져오기 (breadcrumb)
     * @return Menu[]
     */
    public function getMenuPath(Menu $menu): array
    {
        return $this->menuRepository->getMenuPath($menu);
    }

    /**
     * 메뉴 ID로 조회
     */
    public function getMenuById(int $id): ?Menu
    {
        return $this->menuRepository->find($id);
    }

    /**
     * 게시판 연결된 메뉴 찾기
     */
    public function getMenuByBoard(Board $board): ?Menu
    {
        return $this->menuRepository->findByBoardId($board->getId());
    }

    /**
     * 메뉴 생성
     */
    public function createMenu(array $data): Menu
    {
        $menu = new Menu();
        $menu->setMenuName($data['menuName'])
             ->setMenuType($data['menuType'])
             ->setExternalUrl($data['externalUrl'] ?? null)
             ->setDisplayOrder($data['displayOrder'] ?? 0)
             ->setIsVisible($data['isVisible'] ?? true);

        // 부모 메뉴 설정
        if (isset($data['parentId']) && $data['parentId']) {
            $parent = $this->menuRepository->find($data['parentId']);
            if ($parent) {
                $menu->setParent($parent);
            }
        }

        // 게시판 연결
        if (isset($data['boardId']) && $data['boardId']) {
            $board = $this->entityManager->getRepository(Board::class)->find($data['boardId']);
            if ($board) {
                $menu->setBoard($board);
            }
        }

        $this->entityManager->persist($menu);
        $this->entityManager->flush();

        return $menu;
    }

    /**
     * 메뉴 수정
     */
    public function updateMenu(Menu $menu, array $data): Menu
    {
        if (isset($data['menuName'])) {
            $menu->setMenuName($data['menuName']);
        }
        if (isset($data['menuType'])) {
            $menu->setMenuType($data['menuType']);
        }
        if (isset($data['externalUrl'])) {
            $menu->setExternalUrl($data['externalUrl']);
        }
        if (isset($data['displayOrder'])) {
            $menu->setDisplayOrder($data['displayOrder']);
        }
        if (isset($data['isVisible'])) {
            $menu->setIsVisible($data['isVisible']);
        }

        // 부모 메뉴 변경
        if (isset($data['parentId'])) {
            if ($data['parentId']) {
                $parent = $this->menuRepository->find($data['parentId']);
                $menu->setParent($parent);
            } else {
                $menu->setParent(null);
            }
        }

        // 게시판 연결 변경
        if (isset($data['boardId'])) {
            if ($data['boardId']) {
                $board = $this->entityManager->getRepository(Board::class)->find($data['boardId']);
                $menu->setBoard($board);
            } else {
                $menu->setBoard(null);
            }
        }

        $this->entityManager->flush();

        return $menu;
    }

    /**
     * 메뉴 삭제
     */
    public function deleteMenu(Menu $menu): void
    {
        $this->entityManager->remove($menu);
        $this->entityManager->flush();
    }

    /**
     * 메뉴 컨텐츠 설정
     */
    public function setMenuContent(Menu $menu, string $content): MenuContent
    {
        $menuContent = $menu->getContent();

        if (!$menuContent) {
            $menuContent = new MenuContent();
            $menuContent->setMenu($menu);
            $this->entityManager->persist($menuContent);
        }

        $menuContent->setContent($content);
        $this->entityManager->flush();

        return $menuContent;
    }

    /**
     * 메뉴 표시 순서 변경
     */
    public function updateDisplayOrder(Menu $menu, int $newOrder): void
    {
        $menu->setDisplayOrder($newOrder);
        $this->entityManager->flush();
    }

    /**
     * 메뉴 네비게이션 데이터 생성 (프론트엔드용)
     */
    public function buildNavigationData(): array
    {
        $rootMenus = $this->getRootMenus();
        $navigation = [];

        foreach ($rootMenus as $menu) {
            $navigation[] = $this->menuToArray($menu, true);
        }

        return $navigation;
    }

    /**
     * 메뉴를 배열로 변환 (재귀)
     */
    private function menuToArray(Menu $menu, bool $includeChildren = false): array
    {
        $data = [
            'id' => $menu->getId(),
            'name' => $menu->getMenuName(),
            'type' => $menu->getMenuType(),
            'depth' => $menu->getDepth(),
            'order' => $menu->getDisplayOrder(),
        ];

        // 타입별 추가 정보
        if ($menu->getMenuType() === 'board' && $menu->getBoard()) {
            $data['board'] = [
                'id' => $menu->getBoard()->getId(),
                'code' => $menu->getBoard()->getBoardCode(),
                'name' => $menu->getBoard()->getBoardName(),
            ];
        } elseif ($menu->getMenuType() === 'external') {
            $data['url'] = $menu->getExternalUrl();
        } elseif ($menu->getMenuType() === 'html' && $menu->getContent()) {
            $data['hasContent'] = true;
        }

        // 자식 메뉴 포함
        if ($includeChildren && $menu->hasChildren()) {
            $data['children'] = [];
            foreach ($menu->getChildren() as $child) {
                $data['children'][] = $this->menuToArray($child, true);
            }
        }

        return $data;
    }
}
