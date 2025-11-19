<?php

declare(strict_types=1);

namespace Application\Service\Factory;

use Application\Service\AttachmentService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

/**
 * AttachmentService Factory
 */
class AttachmentServiceFactory
{
    public function __invoke(ContainerInterface $container): AttachmentService
    {
        $entityManager = $container->get(EntityManager::class);
        return new AttachmentService($entityManager);
    }
}
