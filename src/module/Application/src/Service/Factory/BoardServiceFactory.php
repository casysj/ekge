<?php

declare(strict_types=1);

namespace Application\Service\Factory;

use Application\Service\BoardService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

/**
 * BoardService Factory
 */
class BoardServiceFactory
{
    public function __invoke(ContainerInterface $container): BoardService
    {
        $entityManager = $container->get(EntityManager::class);
        return new BoardService($entityManager);
    }
}
