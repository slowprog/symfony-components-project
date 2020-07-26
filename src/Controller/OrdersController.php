<?php

namespace App\Controller;

use App\Exception\EntityNotFoundException;
use App\Exception\EntityNotSavedException;
use App\Exception\PaymentFailedException;
use App\Service\OrderService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class OrdersController
{
    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->orderService = $container->get('order.service');
        $this->serializer   = $container->get('serializer');
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
            'data'    => ['orders' => $this->serializer->normalize($this->orderService->getAll())],
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws ExceptionInterface
     */
    public function createAction(Request $request): JsonResponse
    {
        $productsId = $request->get('products_id');

        if (!$productsId) {
            return new JsonResponse([
                'success' => false,
                'data'    => 'The "products_id" parameter cannot be empty',
            ], 400);
        }

        try {
            $order = $this->orderService->create($productsId);
        } catch (EntityNotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'data'    => $e->getMessage(),
            ], 400);
        } catch (EntityNotSavedException $e) {
            return new JsonResponse([
                'success' => false,
                'data'    => $e->getMessage(),
            ], 500);
        }

        return new JsonResponse([
            'success' => true,
            'data'    => ['order' => $this->serializer->normalize($order)],
        ]);
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws ExceptionInterface
     */
    public function paymentAction($id, Request $request): JsonResponse
    {
        $amount = $request->get('amount');

        if (!$amount) {
            return new JsonResponse([
                'success' => false,
                'data'    => 'The "amount" parameter cannot be empty',
            ], 400);
        }

        try {
            $order = $this->orderService->payment($id, $amount);
        } catch (EntityNotFoundException | PaymentFailedException $e) {
            return new JsonResponse([
                'success' => false,
                'data'    => $e->getMessage(),
            ], 400);
        } catch (EntityNotSavedException $e) {
            return new JsonResponse([
                'success' => false,
                'data'    => $e->getMessage(),
            ], 500);
        }

        return new JsonResponse([
            'success' => true,
            'data'    => ['order' => $this->serializer->normalize($order)],
        ]);
    }
}