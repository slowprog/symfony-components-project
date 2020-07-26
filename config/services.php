<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Serializer\OrderNormalizer;
use App\Serializer\ProductNormalizer;
use App\Service\OrderService;
use App\Service\PaymentService;
use App\Service\ProductService;
use GuzzleHttp\Client;
use Symfony\Component\Serializer\Serializer;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set('guzzel', Client::class);

    $services->set('product.service', ProductService::class)
        ->args([service('entity_manager')]);

    $services->set('payment.service', PaymentService::class)
        ->args([service('guzzel')]);

    $services->set('order.service', OrderService::class)
        ->args([service('entity_manager'), service('payment.service')]);

    $services->set('product.normalizer', ProductNormalizer::class);
    $services->set('order.normalizer', OrderNormalizer::class)
        ->args([service('product.normalizer')]);
    $services->set('serializer', Serializer::class)
        ->args([[service('product.normalizer'), service('order.normalizer')]]);
};