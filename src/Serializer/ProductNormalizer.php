<?php

namespace App\Serializer;

use App\Entity\Product;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class ProductNormalizer implements ContextAwareNormalizerInterface
{
    /**
     * @param Product $object
     * @param string  $format
     * @param array   $context
     *
     * @return array
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return [
            'id'    => $object->getId(),
            'name'  => $object->getname(),
            'price' => $object->getPriceForHuman(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Product;
    }
}