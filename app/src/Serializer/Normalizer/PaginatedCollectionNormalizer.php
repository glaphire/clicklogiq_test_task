<?php

namespace App\Serializer\Normalizer;

use App\Pagination\PaginatedCollection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

class PaginatedCollectionNormalizer extends PropertyNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param array $context options that normalizers have access to
     */
    public function supportsNormalization($object, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        return $data;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        return $object instanceof PaginatedCollection;
    }
}