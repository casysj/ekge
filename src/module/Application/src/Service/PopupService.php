<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\Popup;
use Application\Repository\PopupRepository;
use Doctrine\ORM\EntityManager;
use DateTime;

/**
 * 팝업 서비스
 */
class PopupService
{
    private EntityManager $entityManager;
    private PopupRepository $popupRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->popupRepository = $entityManager->getRepository(Popup::class);
    }

    /**
     * 현재 표시 가능한 활성 팝업 가져오기
     */
    public function getActivePopup(): ?Popup
    {
        return $this->popupRepository->findActivePopup();
    }

    /**
     * 모든 팝업 목록
     *
     * @return Popup[]
     */
    public function getAllPopups(): array
    {
        return $this->popupRepository->findAllOrderedByDate();
    }

    /**
     * 팝업 ID로 조회
     */
    public function getPopupById(int $id): ?Popup
    {
        return $this->popupRepository->find($id);
    }

    /**
     * 팝업 생성
     */
    public function createPopup(array $data): Popup
    {
        $popup = new Popup();
        $popup->setTitle($data['title'])
              ->setContent($data['content']);

        if (!empty($data['startDate'])) {
            $popup->setStartDate(new DateTime($data['startDate']));
        }

        if (!empty($data['endDate'])) {
            $popup->setEndDate(new DateTime($data['endDate']));
        }

        $isActive = $data['isActive'] ?? false;

        // 활성화 시 다른 팝업 비활성화
        if ($isActive) {
            $this->deactivateAllPopups();
        }

        $popup->setIsActive($isActive);

        $this->entityManager->persist($popup);
        $this->entityManager->flush();

        return $popup;
    }

    /**
     * 팝업 수정
     */
    public function updatePopup(Popup $popup, array $data): Popup
    {
        if (isset($data['title'])) {
            $popup->setTitle($data['title']);
        }

        if (isset($data['content'])) {
            $popup->setContent($data['content']);
        }

        if (array_key_exists('startDate', $data)) {
            $popup->setStartDate(!empty($data['startDate']) ? new DateTime($data['startDate']) : null);
        }

        if (array_key_exists('endDate', $data)) {
            $popup->setEndDate(!empty($data['endDate']) ? new DateTime($data['endDate']) : null);
        }

        if (isset($data['isActive'])) {
            // 활성화 시 다른 팝업 비활성화
            if ($data['isActive']) {
                $this->deactivateAllPopups($popup->getId());
            }
            $popup->setIsActive($data['isActive']);
        }

        $this->entityManager->flush();

        return $popup;
    }

    /**
     * 팝업 삭제
     */
    public function deletePopup(Popup $popup): void
    {
        $this->entityManager->remove($popup);
        $this->entityManager->flush();
    }

    /**
     * 팝업 활성화/비활성화 토글
     */
    public function toggleActive(Popup $popup): Popup
    {
        $newActiveState = !$popup->isActive();

        // 활성화 시 다른 팝업 비활성화
        if ($newActiveState) {
            $this->deactivateAllPopups($popup->getId());
        }

        $popup->setIsActive($newActiveState);
        $this->entityManager->flush();

        return $popup;
    }

    /**
     * 모든 팝업 비활성화 (특정 ID 제외)
     */
    private function deactivateAllPopups(?int $excludeId = null): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->update(Popup::class, 'p')
           ->set('p.isActive', ':inactive')
           ->where('p.isActive = :active')
           ->setParameter('inactive', false)
           ->setParameter('active', true);

        if ($excludeId !== null) {
            $qb->andWhere('p.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        $qb->getQuery()->execute();
    }
}
