<?php

namespace App\Serializer\Normalizer;

use App\Pagination\PaginatedCollection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PaginatedCollectionNormalizer implements NormalizerInterface
{
    private ObjectNormalizer $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $data['links'] = $object->getLinks();
        $data['total'] = $object->getTotal();
        $data['count'] = $object->getCount();
        $data['items'] = [];

        foreach ($object->getItems() as $item) {
            $data['items'][] = [
                'id' => $item->getId(),
                'reference' => $item->getReference(),
                'name' => $item->getName(),
                'speed' => $item->getSpeed(),
                'is_hazardous' => $item->getIsHazardous(),
                'date' => $item->getDate()->format('Y-m-d'),
            ];
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof PaginatedCollection;
    }
}
