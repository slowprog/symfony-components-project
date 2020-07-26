<?php

namespace App\Serializer;

use App\Entity\Order;
use App\Entity\Product;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class OrderNormalizer implements ContextAwareNormalizerInterface
{
    /**
     * @var ProductNormalizer
     */
    private $productNormalizer;

    /**
     * @param ProductNormalizer $productNormalizer
     */
    public function __construct(ProductNormalizer $productNormalizer)
    {
        $this->productNormalizer = $productNormalizer;
    }

    /**
     * @param Order  $object
     * @param string $format
     * @param array  $context
     *
     * @return array
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return [
            'id'       => $object->getId(),
            'status'   => $object->getStatus(),
            'amount'   => $object->getAmountForHuman(),
            'products' => $object->getProducts()->map(function (Product $product) {
                return $this->productNormalizer->normalize($product);
            })->toArray(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Order;
    }
}