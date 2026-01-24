<?php

declare(strict_types=1);

namespace Application\Repository;

use Application\Entity\Popup;
use Doctrine\ORM\EntityRepository;

/**
 * 팝업 Repository
 */
class PopupRepository extends EntityRepository
{
    /**
     * 현재 표시 가능한 활성 팝업 가져오기
     * 활성화된 팝업 중 날짜 조건에 맞는 것 반환 (1개만)
     */
    public function findActivePopup(): ?Popup
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('(p.startDate IS NULL OR p.startDate <= :now)')
            ->andWhere('(p.endDate IS NULL OR p.endDate >= :now)')
            ->setParameter('active', true)
            ->setParameter('now', $now)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 모든 팝업 목록 (최신순)
     *
     * @return Popup[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 현재 활성화된 팝업이 있는지 확인 (특정 ID 제외)
     */
    public function hasOtherActivePopup(?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.isActive = :active')
            ->setParameter('active', true);

        if ($excludeId !== null) {
            $qb->andWhere('p.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
