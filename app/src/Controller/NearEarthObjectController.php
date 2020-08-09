<?php

namespace App\Controller;

use App\Entity\NearEarthObject;
use App\Pagination\PaginationFactory;
use App\Repository\NearEarthObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NearEarthObjectController extends AbstractFOSRestController
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
        $nearEarthObjectRepository = $this->entityManager
            ->getRepository(NearEarthObject::class);

        $queryBuilder = $nearEarthObjectRepository->findAllQueryBuilder();//->andWhere('n.is_hazardous=1');

        $aa = $queryBuilder->getQuery()->getResult();

        $paginatedCollection = $this
            ->paginationFactory
            ->createCollection($queryBuilder, $request, 'neo_hazardous');

        //$hazardous = $nearEarthObjectRepository->findBy(['is_hazardous' => true]);
        /*
        $qb = $this->getDoctrine()
            ->getRepository('AppBundle:Programmer')
            ->findAllQueryBuilder();
        $paginatedCollection = $this->get('pagination_factory')
            ->createCollection($qb, $request, 'api_programmers_collection');
        $response = $this->createApiResponse($paginatedCollection, 200);
        return $response; */
        $view = $this->view($paginatedCollection, 200)->setFormat('json');

        return $this->handleView($view);
    }

//    protected function createApiResponse($data, $statusCode = 200)
//    {
//        $json = $this->serialize($data);
//
//        return new Response($json, $statusCode, array(
//            'Content-Type' => 'application/json'
//        ));
//    }
}
