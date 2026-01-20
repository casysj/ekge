<?php

declare(strict_types=1);

namespace Application\Controller\Factory;

use Application\Controller\FileController;
use Application\Service\FileUploadService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

/**
 * FileController Factory
 */
class FileControllerFactory
{
    public function __invoke(ContainerInterface $container): FileController
    {
        $fileUploadService = $container->get(FileUploadService::class);
        $entityManager = $container->get(EntityManager::class);

        return new FileController($fileUploadService, $entityManager, 'data/uploads');
    }
}
