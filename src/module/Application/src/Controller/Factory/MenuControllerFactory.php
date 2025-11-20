<?php

declare(strict_types=1);

namespace Application\Controller\Factory;

use Application\Controller\MenuController;
use Application\Service\MenuService;
use Psr\Container\ContainerInterface;

/**
 * MenuController Factory
 */
class MenuControllerFactory
{
    public function __invoke(ContainerInterface $container): MenuController
    {
        $menuService = $container->get(MenuService::class);
        return new MenuController($menuService);
    }
}
