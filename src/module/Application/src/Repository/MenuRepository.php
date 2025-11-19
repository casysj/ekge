<?php

declare(strict_types=1);

namespace Application\Repository;

use Application\Entity\Menu;
use Doctrine\ORM\EntityRepository;

/**
 * 메뉴 Repository
 */
class MenuRepository extends EntityRepository
{
    /**
     * 루트 메뉴만 조회 (보이는 것만, displayOrder 순)
     * @return Menu[]
     */
    public function findRootMenus(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.parent IS NULL')
            ->andWhere('m.isVisible = :visible')
            ->setParameter('visible', true)
            ->orderBy('m.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 전체 메뉴 트리 구조 가져오기
     * @return Menu[]
     */
    public function findMenuTree(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.isVisible = :visible')
            ->setParameter('visible', true)
            ->orderBy('m.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 특정 메뉴의 자식 메뉴 조회
     * @return Menu[]
     */
    public function findChildren(Menu $parent): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.parent = :parent')
            ->andWhere('m.isVisible = :visible')
            ->setParameter('parent', $parent)
            ->setParameter('visible', true)
            ->orderBy('m.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * depth별 메뉴 조회
     * @return Menu[]
     */
    public function findByDepth(int $depth): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.depth = :depth')
            ->andWhere('m.isVisible = :visible')
            ->setParameter('depth', $depth)
            ->setParameter('visible', true)
            ->orderBy('m.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 메뉴 타입별 조회
     * @return Menu[]
     */
    public function findByType(string $menuType): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.menuType = :type')
            ->andWhere('m.isVisible = :visible')
            ->setParameter('type', $menuType)
            ->setParameter('visible', true)
            ->orderBy('m.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 모든 메뉴 (관리자용, 보이지 않는 것 포함)
     * @return Menu[]
     */
    public function findAllForAdmin(): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.parent', 'p')
            ->orderBy('m.depth', 'ASC')
            ->addOrderBy('m.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 메뉴 경로 가져오기 (breadcrumb용)
     * @return Menu[] [root, parent, current] 순서
     */
    public function getMenuPath(Menu $menu): array
    {
        $path = [];
        $current = $menu;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $current->getParent();
        }

        return $path;
    }

    /**
     * 게시판 연결된 메뉴 찾기
     */
    public function findByBoardId(int $boardId): ?Menu
    {
        return $this->createQueryBuilder('m')
            ->where('m.board = :boardId')
            ->andWhere('m.isVisible = :visible')
            ->setParameter('boardId', $boardId)
            ->setParameter('visible', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
