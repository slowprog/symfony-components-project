<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="products")
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * Цена в копейках.
     *
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Order", mappedBy="products")
     */
    private $orders;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Product
     */
    public function setName($name): Product
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getPriceForHuman(): string
    {
        return (string)round($this->price / 100, 2);
    }

    /**
     * @param int $price
     *
     * @return Product
     */
    public function setPrice($price): Product
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    /**
     * @param Collection $orders
     *
     * @return Product
     */
    public function setOrders(Collection $orders): Product
    {
        $this->orders = $orders;

        return $this;
    }
}