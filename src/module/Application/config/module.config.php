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
            // 메뉴 API
            'api-menus' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/api/menus',
                    'defaults' => [
                        'controller' => Controller\MenuController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'api-menu-view' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/menus/:id',
                    'defaults' => [
                        'controller' => Controller\MenuController::class,
                        'action'     => 'view',
                    ],
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                ],
            ],
            // 관리자 API
            'api-admin-login' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/api/admin/login',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
            'api-admin-logout' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/api/admin/logout',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
            'api-admin-me' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/api/admin/me',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'me',
                    ],
                ],
            ],
            'api-admin-posts-create' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/api/admin/posts',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'createPost',
                    ],
                ],
            ],
            'api-admin-posts-update' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/admin/posts/:id',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'updatePost',
                    ],
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                ],
            ],
            'api-admin-upload' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/api/admin/upload',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'upload',
                    ],
                ],
            ],
            'api-admin-delete-attachment' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/admin/attachments/:id',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'deleteAttachment',
                    ],
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                ],
            ],
            'api-admin-stats' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/api/admin/stats',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'stats',
                    ],
                ],
            ],
            // 파일 서빙
            'api-file-serve' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/files/:id',
                    'defaults' => [
                        'controller' => Controller\FileController::class,
                        'action'     => 'serve',
                    ],
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                ],
            ],
            'api-file-serve-by-path' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/files/path/:path',
                    'defaults' => [
                        'controller' => Controller\FileController::class,
                        'action'     => 'serveByPath',
                    ],
                ],
            ],
            // 팝업 API (공개)
            'api-popup-active' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/api/popup/active',
                    'defaults' => [
                        'controller' => Controller\PopupController::class,
                        'action'     => 'active',
                    ],
                ],
            ],
            // 팝업 관리자 API
            'api-admin-popups' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/api/admin/popups',
                    'defaults' => [
                        'controller' => Controller\PopupController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'api-admin-popup-item' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/admin/popups/:id',
                    'defaults' => [
                        'controller' => Controller\PopupController::class,
                        'action'     => 'item',
                    ],
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                ],
            ],
            'api-admin-popup-toggle' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/admin/popups/:id/toggle',
                    'defaults' => [
                        'controller' => Controller\PopupController::class,
                        'action'     => 'toggle',
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
            Controller\AdminController::class => Controller\Factory\AdminControllerFactory::class,
            Controller\MenuController::class => Controller\Factory\MenuControllerFactory::class,
            Controller\FileController::class => Controller\Factory\FileControllerFactory::class,
            Controller\PopupController::class => Controller\Factory\PopupControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\BoardService::class => Service\Factory\BoardServiceFactory::class,
            Service\PopupService::class => Service\Factory\PopupServiceFactory::class,
            Service\PostService::class => Service\Factory\PostServiceFactory::class,
            Service\AuthenticationService::class => Service\Factory\AuthenticationServiceFactory::class,
            Service\FileUploadService::class => Service\Factory\FileUploadServiceFactory::class,
            Service\AttachmentService::class => Service\Factory\AttachmentServiceFactory::class,
            Service\MenuService::class => Service\Factory\MenuServiceFactory::class,
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
        'strategies' => [
            'ViewJsonStrategy', // JSON 응답 처리
        ],
    ],
];
