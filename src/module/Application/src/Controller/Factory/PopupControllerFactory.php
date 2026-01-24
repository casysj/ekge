<?php

declare(strict_types=1);

namespace Application\Controller\Factory;

use Application\Controller\PopupController;
use Application\Service\AuthenticationService;
use Application\Service\PopupService;
use Psr\Container\ContainerInterface;

/**
 * PopupController Factory
 */
class PopupControllerFactory
{
    public function __invoke(ContainerInterface $container): PopupController
    {
        $authService = $container->get(AuthenticationService::class);
        $popupService = $container->get(PopupService::class);

        return new PopupController($authService, $popupService);
    }
}
