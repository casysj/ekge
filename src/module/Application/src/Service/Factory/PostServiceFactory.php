<?php

declare(strict_types=1);

namespace Application\Service\Factory;

use Application\Service\PostService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

/**
 * PostService Factory
 */
class PostServiceFactory
{
    public function __invoke(ContainerInterface $container): PostService
    {
        $entityManager = $container->get(EntityManager::class);
        return new PostService($entityManager);
    }
}
