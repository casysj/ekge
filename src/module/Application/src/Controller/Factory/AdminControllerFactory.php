<?php

declare(strict_types=1);

namespace Application\Controller\Factory;

use Application\Controller\AdminController;
use Application\Service\AuthenticationService;
use Application\Service\BoardService;
use Application\Service\PostService;
use Application\Service\FileUploadService;
use Psr\Container\ContainerInterface;

/**
 * AdminController Factory
 */
class AdminControllerFactory
{
    public function __invoke(ContainerInterface $container): AdminController
    {
        $authService = $container->get(AuthenticationService::class);
        $boardService = $container->get(BoardService::class);
        $postService = $container->get(PostService::class);
        $fileUploadService = $container->get(FileUploadService::class);

        return new AdminController($authService, $boardService, $postService, $fileUploadService);
    }
}
