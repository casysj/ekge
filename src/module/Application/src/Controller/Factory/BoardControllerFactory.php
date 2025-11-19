<?php

declare(strict_types=1);

namespace Application\Controller\Factory;

use Application\Controller\BoardController;
use Application\Service\BoardService;
use Application\Service\PostService;
use Psr\Container\ContainerInterface;

/**
 * BoardController Factory
 */
class BoardControllerFactory
{
    public function __invoke(ContainerInterface $container): BoardController
    {
        $boardService = $container->get(BoardService::class);
        $postService = $container->get(PostService::class);

        return new BoardController($boardService, $postService);
    }
}
