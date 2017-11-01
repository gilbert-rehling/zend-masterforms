<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
/**
 * @see https://samsonasik.wordpress.com/2014/10/15/zend-framework-2-using-doctrine-extension-with-doctrinemodule-and-doctrineormmodule/
 *
 * "minimum-stability": "dev",
 * "prefer-stable": true,
 */
namespace Masterforms;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Zend\ServiceManager\Factory\InvokableFactory;

use Masterforms\Doctrine\Extension\Replace;

return [
    'router' => [
        'routes' => [
            'masterforms' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/masterforms',
                    'defaults' => [
                        'controller' => Controller\MasterformsController::class,
                        'action' => 'masterforms'
                    ]
                ],
                'child_routes' => [
                    'error' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/error',
                            'defaults' => [
                                'controller' => Controller\MasterformsController::class,
                                'action' => 'error'
                            ]
                        ]
                    ],
                    'admin' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/admin',
                            'defaults' => [
                                'controller' => Controller\MasterformsAdminController::class,
                                'action' => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'setup' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/setup',
                                    'defaults' => [
                                        'controller' => Controller\MasterformsSetupController::class,
                                        'action' => 'setup',
                                    ]
                                ]
                            ],
                            'forms' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/forms',
                                    'defaults' => [
                                        'controller' => Controller\MasterformsController::class,
                                        'action' => 'directory',
                                    ]
                                ]
                            ],
                            'fields' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/fields',
                                    'defaults' => [
                                        'controller' => Controller\MasterformsController::class,
                                        'action' => 'directory',
                                    ]
                                ]
                            ],
                            'fieldsets' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/fieldsets',
                                    'defaults' => [
                                        'controller' => Controller\MasterformsController::class,
                                        'action' => 'directory',
                                    ]
                                ]
                            ],
                            'categories' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/categories',
                                    'defaults' => [
                                        'controller' => Controller\MasterformsController::class,
                                        'action' => 'directory',
                                    ]
                                ]
                            ],
                            'formfields' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/formfields',
                                    'defaults' => [
                                        'controller' => Controller\MasterformsController::class,
                                        'action' => 'directory',
                                    ]
                                ]
                            ],
                            'data' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/data',
                                    'defaults' => [
                                        'controller' => Controller\MasterformsController::class,
                                        'action' => 'directory',
                                    ]
                                ]
                            ],
                            'help' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/help',
                                    'defaults' => [
                                        'controller' => Controller\MasterformsController::class,
                                        'action' => 'directory',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\MasterformsController::class => Controller\Factory\MasterformsControllerFactory::class,
            Controller\MasterformsSetupController::class => Controller\Factory\MasterformsSetupControllerFactory::class
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\MasterformsService::class => Service\Factory\MasterformsServiceFactory::class,
            Service\MasterformsSetupService::class => Service\Factory\MasterformsSetupServiceFactory::class
        ]
    ],
    'masterforms_options' => [
        'welcomeTitle' => 'Welcome To Masterforms',
        'errorsTitle' => 'Masterforms Error Reporting',
        'setupTitle' => 'Masterforms Setup',
        'allow_user_access' => 1
    ],
    'doctrine' => [
        // Configuration details for the ORM.
        // See http://docs.doctrine-project.org/en/latest/reference/configuration.html
        'configuration' => [
            'orm_default' => [
                // Custom DQL functions.
                // You can grab common MySQL ones at https://github.com/beberlei/DoctrineExtensions
                // Further docs at http://docs.doctrine-project.org/en/latest/cookbook/dql-user-defined-functions.html
                'datetime_functions' => [
                    'DATEDIFF'      => 'DoctrineExtensions\Query\Mysql\DateDiff',
                    'STRTODATE'     => 'DoctrineExtensions\Query\Mysql\StrToDate',
                    'DATE'          => 'DoctrineExtensions\Query\Mysql\Date',
                    'TIMESTAMPDIFF' => 'DoctrineExtensions\Query\Mysql\TimestampDiff',
                    'UNIXTIMESTAMP' => 'DoctrineExtensions\Query\Mysql\UnixTimestamp'
                ],
                'string_functions' => [
                    'replace' => Replace::class
                ],
                'numeric_functions' => []
            ]
        ],
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Entity']
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ]
        ]
    ],
    'doctrine_factories' => [
        Doctrine\ORM\EntityManager::class => Doctrine\Factory\Service\EntityManagerFactory::class
    ],
    'view_manager' => [
        'template_map' => [
            'masterforms/masterforms/masterforms' => __DIR__ . '/../view/masterforms/masterforms/masterforms.phtml',
            'masterforms/admin/index' => __DIR__ . '/../view/masterforms/admin/index.phtml',
            'masterforms/admin/setup' => __DIR__ . '/../view/masterforms/admin/setup.phtml'
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'title' => View\Helper\Title::class,
        ]
    ]
];
