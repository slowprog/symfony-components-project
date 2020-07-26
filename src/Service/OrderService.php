<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Product;
use App\Exception\EntityNotSavedException;
use App\Exception\EntityNotFoundException;
use App\Exception\PaymentFailedException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;

class OrderService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @param EntityManager  $entityManager
     * @param PaymentService $paymentService
     */
    public function __construct(EntityManager $entityManager, PaymentService $paymentService)
    {
        $this->entityManager  = $entityManager;
        $this->paymentService = $paymentService;
    }

    /**
     * @return Product[]
     */
    public function getAll(): array
    {
        return $this->entityManager->getRepository(Order::class)->findAll();
    }

    /**
     * @param int|int[] $productsId
     *
     * @return Order
     *
     * @throws EntityNotSavedException
     * @throws EntityNotFoundException
     */
    public function create($productsId): Order
    {
        $productsId = is_array($productsId) ? $productsId : [$productsId];

        /** @var Product[] $products */
        $products = $this->entityManager->getRepository(Product::class)->findBy(['id' => $productsId]);

        if (!$products) {
            throw new EntityNotFoundException(sprintf('Products #%s not found', implode(', #', $productsId)));
        }

        $order = new Order();

        foreach ($products as $product) {
            $order->addProduct($product);
        }

        try {
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            throw new EntityNotSavedException($e->getMessage());
        }

        return $order;
    }

    /**
     * @param int   $id
     * @param float $amount
     *
     * @return Order
     *
     * @throws EntityNotFoundException
     * @throws PaymentFailedException
     * @throws EntityNotSavedException
     */
    public function payment($id, $amount): Order
    {
        /** @var Order $order */
        $order = $this->entityManager->getRepository(Order::class)->find($id);

        if (!$order) {
            throw new EntityNotFoundException(sprintf('Order #%s not found', $id));
        }

        if (!$order->isStatusNew()) {
            throw new PaymentFailedException(sprintf('Order #%s not new', $id));
        }

        $amountNormalize = (int)($amount * 100);

        if ($order->getAmount() !== $amountNormalize) {
            throw new PaymentFailedException(sprintf('The amount of order #%s incorrect', $id));
        }

        if (!$this->paymentService->pay()) {
            throw new PaymentFailedException(sprintf('Payment for order #%s failed', $id));
        }

        $order->setStatus(Order::STATUS_PAID);

        try {
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            throw new EntityNotSavedException($e->getMessage());
        }

        return $order;
    }
}