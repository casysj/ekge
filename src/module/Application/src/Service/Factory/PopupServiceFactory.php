<?php

declare(strict_types=1);

namespace Application\Service\Factory;

use Application\Service\PopupService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

/**
 * PopupService Factory
 */
class PopupServiceFactory
{
    public function __invoke(ContainerInterface $container): PopupService
    {
        $entityManager = $container->get(EntityManager::class);
        return new PopupService($entityManager);
    }
}
