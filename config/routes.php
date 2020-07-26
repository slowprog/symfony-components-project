<?php

use App\Controller\OrdersController;
use App\Controller\ProductsController;

return [
    'products.index'    => [
        'path'   => '/products',
        'class'  => ProductsController::class,
        'action' => 'indexAction',
        'method' => 'GET',
    ],
    'products.generate' => [
        'path'   => '/products/generate',
        'class'  => ProductsController::class,
        'action' => 'generateAction',
        'method' => 'POST',
    ],
    'orders.index'      => [
        'path'   => '/orders',
        'class'  => OrdersController::class,
        'action' => 'indexAction',
        'method' => 'GET',
    ],
    'orders.create'     => [
        'path'   => '/orders',
        'class'  => OrdersController::class,
        'action' => 'createAction',
        'method' => 'POST',
    ],
    'orders.payment'    => [
        'path'   => '/orders/{id}/payment',
        'class'  => OrdersController::class,
        'action' => 'paymentAction',
        'method' => 'POST',
    ],
];