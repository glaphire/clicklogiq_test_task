<?php

namespace App\Controller;

use App\Entity\NearEarthObject;
use App\Pagination\PaginationFactory;
use App\Repository\NearEarthObjectRepository;
use App\Serializer\Normalizer\PaginatedCollectionNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class NearEarthObjectController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    /**
     * @var PaginationFactory
     */
    private $paginationFactory;

    public function __construct(EntityManagerInterface $entityManager, PaginationFactory $paginationFactory)
    {
        $this->entityManager = $entityManager;
        $this->paginationFactory = $paginationFactory;
    }

    /**
     * @Route("/neo/hazardous", name="neo_hazardous", methods={"GET"})
     */
    public function hazardousAction(Request $request, ObjectNormalizer $objectNormalizer)
    {
        /**
         * @var NearEarthObjectRepository $nearEarthObjectRepository
         */
        $nearEarthObjectRepository = $this
            ->entityManager
            ->getRepository(NearEarthObject::class);

        //TODO: add getting is_hazardous=1
        $queryBuilder = $nearEarthObjectRepository->findAllQueryBuilder();

        $paginatedCollection = $this
            ->paginationFactory
            ->createCollection($queryBuilder, $request, 'neo_hazardous');

        $serializer = new Serializer([new PaginatedCollectionNormalizer($objectNormalizer)], [new JsonEncoder()]);
        $serializedPaginatedCollection = $serializer->serialize($paginatedCollection, 'json');

        return new Response($serializedPaginatedCollection, 200, [
            'Content-Type' => 'application/json',
        ]);
    }
}
