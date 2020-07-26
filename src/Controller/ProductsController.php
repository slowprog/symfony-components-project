<?php

namespace App\Controller;

use App\Exception\EntityNotSavedException;
use App\Service\ProductService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ProductsController
{
    /**
     * @var ProductService
     */
    private $productService;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->productService = $container->get('product.service');
        $this->serializer     = $container->get('serializer');
    }

    /**
     * @return JsonResponse
     *
     * @throws ExceptionInterface
     */
    public function indexAction(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data'    => ['products' => $this->serializer->normalize($this->productService->getAll())],
        ]);
    }

    /**
     * @return JsonResponse
     *
     * @throws ExceptionInterface
     */
    public function generateAction(): JsonResponse
    {
        try {
            return new JsonResponse([
                'success' => true,
                'data'    => ['products' => $this->serializer->normalize($this->productService->createForTest())],
            ]);
        } catch (EntityNotSavedException $e) {
            return new JsonResponse([
                'success' => false,
                'data'    => $e->getMessage(),
            ], 500);
        }
    }
}