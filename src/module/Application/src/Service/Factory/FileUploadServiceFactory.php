<?php

declare(strict_types=1);

namespace Application\Service\Factory;

use Application\Service\FileUploadService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

/**
 * FileUploadService Factory
 */
class FileUploadServiceFactory
{
    public function __invoke(ContainerInterface $container): FileUploadService
    {
        $entityManager = $container->get(EntityManager::class);

        // 설정에서 업로드 경로 가져오기 (선택사항)
        $config = $container->get('config');
        $uploadPath = $config['upload_path'] ?? 'data/uploads';
        $maxFileSize = $config['max_file_size'] ?? 10485760; // 10MB

        return new FileUploadService($entityManager, $uploadPath, $maxFileSize);
    }
}
