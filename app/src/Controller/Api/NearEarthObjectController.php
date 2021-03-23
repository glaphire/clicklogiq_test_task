<?php

namespace App\Controller\Api;

use App\Entity\NearEarthObject;
use App\Pagination\PaginationFactory;
use App\Repository\NearEarthObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;

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
            ->createQueryBuilder('neo')
            ->addCriteria(NearEarthObjectRepository::createIsHazardousCriteria(true));

        $paginatedCollection = $this
            ->paginationFactory
            ->createCollection($queryBuilder, $request, 'neo_hazardous');

        return $this->json($paginatedCollection, 200, [], ['datetime_format' => 'Y-m-d']);
    }

    /**
     * @Route("/neo/fastest", name="neo_fastest", methods={"GET"})
     */
    public function getFastestNearEarthObject(Request $request, bool $isHazardous)
    {
        /**
         * @var NearEarthObjectRepository $nearEarthObjectRepository
         */
        $nearEarthObjectRepository = $this
            ->entityManager
            ->getRepository(NearEarthObject::class);

        $fastestNearEarthObject = $nearEarthObjectRepository
            ->getFastestNearEarthObject($isHazardous);

        return $this->json($fastestNearEarthObject, 200, [], ['datetime_format' => 'Y-m-d']);
    }

    /**
     * @Route("/neo/best-month", name="neo_best_month", methods={"GET"})
     */
    public function getMonthWithMostNearEarthObjects(Request $request, bool $isHazardous)
    {
        /**
         * @var NearEarthObjectRepository $nearEarthObjectRepository
         */
        $nearEarthObjectRepository = $this
            ->entityManager
            ->getRepository(NearEarthObject::class);

        $month = $nearEarthObjectRepository->getMonthWithMostNearEarthObjects($isHazardous);

        return $this->json($month, 200, [], ['datetime_format' => 'Y-m-d']);
    }
}
