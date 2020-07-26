<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * @ORM\Entity
 * @ORM\Table(name="orders")
 */
class Order
{
    /**
     * @var string
     */
    public const STATUS_NEW = 'new';

    /**
     * @var string
     */
    public const STATUS_PAID = 'paid';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Product", inversedBy="orders")
     */
    private $products;

    /**
     * Order constructor.
     */
    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->status   = self::STATUS_NEW;
        $this->amount   = 0;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isStatusNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Order
     */
    public function setStatus($status): Order
    {
        if (!in_array($status, $this->getStatusesList(), true)) {
            throw new InvalidArgumentException(sprintf('Status "%s" not exists', $status));
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * @param Product $product
     *
     * @return Order
     */
    public function addProduct(Product $product): Order
    {
        $this->products->add($product);

        $this->amount += $product->getPrice();

        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getAmountForHuman(): string
    {
        return (string)round($this->amount / 100, 2);
    }

    /**
     * @return string[]
     */
    private function getStatusesList(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_PAID,
        ];
    }
}