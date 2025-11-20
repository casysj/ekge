<?php

declare(strict_types=1);

namespace Application\Service\Factory;

use Application\Service\MenuService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

/**
 * MenuService Factory
 */
class MenuServiceFactory
{
    public function __invoke(ContainerInterface $container): MenuService
    {
        $entityManager = $container->get(EntityManager::class);
        return new MenuService($entityManager);
    }
}
