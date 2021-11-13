<?php

namespace App\Controller\Api;

use App\Entity\NearEarthObject;
use App\Pagination\PaginationFactory;
use App\Repository\NearEarthObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NearEarthObjectController extends AbstractController
{
    private const RESPONSE_DATETIME_FORMAT = 'Y-m-d';

    private EntityManagerInterface $entityManager;

    private PaginationFactory $paginationFactory;

    public function __construct(EntityManagerInterface $entityManager, PaginationFactory $paginationFactory)
    {
        $this->entityManager = $entityManager;
        $this->paginationFactory = $paginationFactory;
    }

    /**
     * @Route("/neo/hazardous", name="neo_hazardous", methods={"GET"})
     */
    public function hazardousAction(Request $request): Response
    {
        /**
         * @var NearEarthObjectRepository $nearEarthObjectRepository
         */
        $nearEarthObjectRepository = $this
            ->entityManager
            ->getRepository(NearEarthObject::class);

        $queryBuilder = $nearEarthObjectRepository
            ->createQueryBuilder('neo')
            ->addCriteria(NearEarthObjectRepository::createIsHazardousCriteria(true));

        $paginatedCollection = $this
            ->paginationFactory
            ->createCollection($queryBuilder, $request, 'neo_hazardous');

        return $this->prepareResponse($paginatedCollection);
    }

    /**
     * @Route("/neo/fastest", name="neo_fastest", methods={"GET"})
     */
    public function getFastestNearEarthObject(bool $isHazardous): Response
    {
        /**
         * @var NearEarthObjectRepository $nearEarthObjectRepository
         */
        $nearEarthObjectRepository = $this
            ->entityManager
            ->getRepository(NearEarthObject::class);

        $fastestNearEarthObject = $nearEarthObjectRepository
            ->getFastestNearEarthObject($isHazardous);

        return $this->prepareResponse($fastestNearEarthObject);
    }

    /**
     * @Route("/neo/best-month", name="neo_best_month", methods={"GET"})
     */
    public function getMonthWithMostNearEarthObjects(bool $isHazardous): Response
    {
        /**
         * @var NearEarthObjectRepository $nearEarthObjectRepository
         */
        $nearEarthObjectRepository = $this
            ->entityManager
            ->getRepository(NearEarthObject::class);

        $monthName = $nearEarthObjectRepository->getMonthWithMostNearEarthObjects($isHazardous);

        return $this->prepareResponse(['best_month' => $monthName]);
    }

    /**
     * @param mixed $data
     */
    private function prepareResponse($data, int $status = Response::HTTP_OK, $headers = [], $context = []): Response
    {
        $datetimeFormat = ['datetime_format' => self::RESPONSE_DATETIME_FORMAT];
        $resultContext = array_merge($datetimeFormat, $context);

        return $this->json($data, $status, $headers, $resultContext);
    }
}
