<?php

declare(strict_types=1);

namespace Application\Service\Factory;

use Application\Service\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

/**
 * AuthenticationService Factory
 */
class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationService
    {
        $entityManager = $container->get(EntityManager::class);
        return new AuthenticationService($entityManager);
    }
}
