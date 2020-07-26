<?php

namespace App\Service;

use App\Entity\Product;
use App\Exception\EntityNotSavedException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;

class ProductService
{
    /**
     * @var int
     */
    private const COUNT_PRODUCTS = 20;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Product[]
     */
    public function getAll(): array
    {
        return $this->entityManager->getRepository(Product::class)->findAll();
    }

    /**
     * @param int $count
     *
     * @return Product[]
     *
     * @throws EntityNotSavedException
     */
    public function createForTest($count = self::COUNT_PRODUCTS): array
    {
        try {
            $products = [];

            for ($i = 0; $i < $count; $i++) {
                $product = (new Product())
                    ->setName('Product #' . ($i + 1))
                    ->setPrice(($i + 1000) * 10)
                ;

                $this->entityManager->persist($product);

                $products[] = $product;
            }

            $this->entityManager->flush();

            return $products;
        } catch (ORMException $e) {
            throw new EntityNotSavedException($e->getMessage());
        }
    }
}