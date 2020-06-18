<?php

return [
    '__name' => 'admin-product-category',
    '__version' => '0.1.0',
    '__git' => 'git@github.com:getmim/admin-product-category.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/admin-product-category' => ['install','update','remove'],
        'theme/admin/product/category' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'admin' => NULL
            ],
            [
                'lib-formatter' => NULL
            ],
            [
                'lib-form' => NULL
            ],
            [
                'lib-pagination' => NULL
            ],
            [
                'admin-site-meta' => NULL
            ],
            [
                'product-category' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'AdminProductCategory\\Controller' => [
                'type' => 'file',
                'base' => 'modules/admin-product-category/controller'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'admin' => [
            'adminProductCategory' => [
                'path' => [
                    'value' => '/product/category'
                ],
                'method' => 'GET',
                'handler' => 'AdminProductCategory\\Controller\\Category::index'
            ],
            'adminProductCategoryEdit' => [
                'path' => [
                    'value' => '/product/category/(:id)',
                    'params' => [
                        'id'  => 'number'
                    ]
                ],
                'method' => 'GET|POST',
                'handler' => 'AdminProductCategory\\Controller\\Category::edit'
            ],
            'adminProductCategoryRemove' => [
                'path' => [
                    'value' => '/product/category/(:id)/remove',
                    'params' => [
                        'id'  => 'number'
                    ]
                ],
                'method' => 'GET',
                'handler' => 'AdminProductCategory\\Controller\\Category::remove'
            ]
        ]
    ],
    'adminUi' => [
        'sidebarMenu' => [
            'items' => [
                'product' => [
                    'label' => 'Product',
                    'icon' => '<i class="fas fa-box-open"></i>',
                    'priority' => 0,
                    'filterable' => false,
                    'children' => [
                        'category' => [
                            'label' => 'Category',
                            'icon'  => '<i></i>',
                            'route' => ['adminProductCategory'],
                            'perms' => 'manage_product_category'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'libForm' => [
        'forms' => [
            'admin.product.edit' => [
                'category' => [
                    'label' => 'Category',
                    'type' => 'checkbox-tree',
                    'rules' => []
                ]
            ],
            'admin.product-category.edit' => [
                '@extends' => ['std-site-meta'],
                'name' => [
                    'label' => 'Name',
                    'type' => 'text',
                    'rules' => [
                        'required' => true
                    ]
                ],
                'slug' => [
                    'label' => 'Slug',
                    'type' => 'text',
                    'slugof' => 'name',
                    'rules' => [
                        'required' => TRUE,
                        'empty' => FALSE,
                        'unique' => [
                            'model' => 'ProductCategory\\Model\\ProductCategory',
                            'field' => 'slug',
                            'self' => [
                                'service' => 'req.param.id',
                                'field' => 'id'
                            ]
                        ]
                    ]
                ],
                'parent' => [
                    'label' => 'Parent',
                    'type' => 'radio-tree',
                    'rules' => [
                        'exists' => [
                            'model' => 'ProductCategory\\Model\\ProductCategory',
                            'field' => 'id'
                        ]
                    ]
                ],
                'content' => [
                    'label' => 'About',
                    'type' => 'summernote',
                    'rules' => []
                ],
                'meta-schema' => [
                    'options' => ['ItemList' => 'ItemList']
                ]
            ],
            'admin.product-category.index' => [
                'q' => [
                    'label' => 'Search',
                    'type' => 'search',
                    'nolabel' => true,
                    'rules' => []
                ]
            ]
        ]
    ]
];
