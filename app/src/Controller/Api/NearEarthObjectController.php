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
            ->isHazardousQueryBuilder(true);

        $paginatedCollection = $this
            ->paginationFactory
            ->createCollection($queryBuilder, $request, 'neo_hazardous');

        return $this->json($paginatedCollection, 200, [], ['datetime_format' => 'Y-m-d']);
    }

    /**
     * @Route("/neo/fastest", name="neo_fastest", methods={"GET"})
     */
    public function getFastestNearEarthObject(Request $request)
    {
        throw new \Exception('1111');
        $constraints = new Collection([
            'hazardous' => [new Optional(new Type(['type' => 'boolean']))],
        ]);

        //TODO: refactor to DTO and argument resolvers
        //TODO: See "Symfony 5 Deep Dive! The HttpKernel Request-Response Flow" course
        $errors = $this->validate($request->get('hazardous', false), $constraints);

        //TODO: refactor to normal errors response
        if ($errors) {
            return $this->json($errors, 400);
        }

        $isHazardous = filter_var($request->get('hazardous', false), FILTER_VALIDATE_BOOLEAN);

        /**
         * @var NearEarthObjectRepository $nearEarthObjectRepository
         */
        $nearEarthObjectRepository = $this
            ->entityManager
            ->getRepository(NearEarthObject::class);

        //TODO: refactor to Criteria instead of QueryBuilder
        $queryBuilder = $nearEarthObjectRepository
            ->isHazardousQueryBuilder($isHazardous);

        $fastestNearEarthObject = $nearEarthObjectRepository
            ->getFastestNearEarthObject($queryBuilder);

        return $this->json($fastestNearEarthObject, 200, [], ['datetime_format' => 'Y-m-d']);
    }

    /**
     * @Route("/neo/best-month", name="neo_best_month", methods={"GET"})
     */
    public function getMonthWithMostNearEarthObjects(Request $request)
    {
        $isHazardous = filter_var($request->get('hazardous', false), FILTER_VALIDATE_BOOLEAN);

        /**
         * @var NearEarthObjectRepository $nearEarthObjectRepository
         */
        $nearEarthObjectRepository = $this
            ->entityManager
            ->getRepository(NearEarthObject::class);

        $queryBuilder = $nearEarthObjectRepository
            ->isHazardousQueryBuilder($isHazardous);

        //TODO: write rest of endpoint logic
    }

    /**
     * @param $value
     * @param $constraints
     *
     * @return array|void
     */
    protected function validate($value, $constraints)
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($value, $constraints);
        $messages = [];

        if (0 === count($violations)) {
            return;
        }

        foreach ($violations as $violation) {
            /* @var ConstraintViolation $violation */
            $messages[] = $violation->getMessage();
        }

        return $messages;
    }
}
