<?php

declare(strict_types=1);

namespace Application;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'test' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/test',
                    'defaults' => [
                        'controller' => Controller\TestController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            // API 라우트
            'api-boards' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/api/boards',
                    'defaults' => [
                        'controller' => Controller\BoardController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'api-board-posts' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/boards/:code/posts',
                    'defaults' => [
                        'controller' => Controller\BoardController::class,
                        'action'     => 'list',
                    ],
                ],
            ],
            'api-post-view' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/posts/:id',
                    'defaults' => [
                        'controller' => Controller\BoardController::class,
                        'action'     => 'view',
                    ],
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
            Controller\TestController::class => ReflectionBasedAbstractFactory::class,
            Controller\BoardController::class => Controller\Factory\BoardControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\BoardService::class => Service\Factory\BoardServiceFactory::class,
            Service\PostService::class => Service\Factory\PostServiceFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
