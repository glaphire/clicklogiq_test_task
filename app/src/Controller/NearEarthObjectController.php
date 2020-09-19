<?php

namespace App\Controller;

use App\Entity\NearEarthObject;
use App\Pagination\PaginationFactory;
use App\Repository\NearEarthObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function hazardousAction(Request $request)
    {
        /**
         * @var NearEarthObjectRepository $nearEarthObjectRepository
         */
        $nearEarthObjectRepository = $this
            ->entityManager
            ->getRepository(NearEarthObject::class);

        $queryBuilder = $nearEarthObjectRepository
            ->isHazardousQueryBuilder(true);

        $paginatedCollection = $this
            ->paginationFactory
            ->createCollection($queryBuilder, $request, 'neo_hazardous');

        return $this->json($paginatedCollection, 200, [], ['datetime_format' => 'Y-m-d']);
    }

    /**
     * @Route("/neo/fastest", name="neo_fastest", methods={"GET"})
     */
    public function getFastestNearEarthObject(Request $request, ValidatorInterface $validator)
    {
        $isHazardous = $request->get('hazardous', false);

        /**
         * @var NearEarthObjectRepository $nearEarthObjectRepository
         */
        $nearEarthObjectRepository = $this
            ->entityManager
            ->getRepository(NearEarthObject::class);

        //TODO: refactor isHazardousQueryBuilder to Criteria and filter Fastest objects by is_hazardous
        $fastestNearEarthObject = $nearEarthObjectRepository->getFastestNearEarthObject();

        //TODO: make unified response class
        return $this->json($fastestNearEarthObject, 200, [], ['datetime_format' => 'Y-m-d']);
    }
}
