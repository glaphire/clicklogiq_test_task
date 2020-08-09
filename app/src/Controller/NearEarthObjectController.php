<?php

namespace App\Controller;

use App\Entity\NearEarthObject;
use App\Pagination\PaginationFactory;
use App\Repository\NearEarthObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class NearEarthObjectController extends AbstractFOSRestController
{
    private EntityManagerInterface $entityManager;
    /**
     * @var PaginationFactory
     */
    private $paginationFactory;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, PaginationFactory $paginationFactory, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->paginationFactory = $paginationFactory;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/neo/hazardous", name="neo_hazardous", methods={"GET"})
     */
    public function hazardousAction(Request $request)
    {
        /**
         * @var NearEarthObjectRepository $nearEarthObjectRepository
         */
        $nearEarthObjectRepository = $this->entityManager
            ->getRepository(NearEarthObject::class);

        //TODO: add getting is_hazardous=1
        $queryBuilder = $nearEarthObjectRepository->findAllQueryBuilder();

        $paginatedCollection = $this
            ->paginationFactory
            ->createCollection($queryBuilder, $request, 'neo_hazardous');

        $view = $this->view($paginatedCollection, 200)->setFormat('json');

        return $this->handleView($view);
    }
}
